<?php

namespace App\Http\Controllers;

use App\Models\Audit;
use App\Models\Order;
use App\Models\Promo;
use App\Paysera\WebToPay;
use App\Paysera\WebToPayException;
use App\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PayseraCallbackController extends Controller
{
    /**
     * Обработка callback от Paysera
     */
    public function callback(Request $request, EmailService $emailService)
    {
        try {
            // Валидируем и парсим данные от Paysera
            $response = WebToPay::validateAndParseData(
                $request->all(),
                config('payment.paysera.project_id'),
                config('payment.paysera.sign_password')
            );

            Log::info('Paysera callback received', [
                'response' => $response,
                'orderid' => $response['orderid'] ?? 'unknown'
            ]);

            // Проверяем статус платежа
            // status = 1 - успешный платеж
            // status = 2 - платеж принят, но не подтвержден
            // status = 3 - дополнительная информация
            // status = 0 - платеж отменен или не выполнен
            if (isset($response['status']) && ($response['status'] === '1' || $response['status'] === 1)) {
                // Получаем ID заказа
                $orderId = $response['orderid'];
                
                // Находим заказ в базе данных
                $order = DB::table('orders_')->where('id', $orderId)->first();
                
                if (!$order) {
                    Log::error('Paysera callback: Order not found', ['orderid' => $orderId]);
                    return response('Order not found', 404);
                }

                // Проверяем, что заказ еще не был обработан (защита от повторной обработки)
                if ($order->order_status != 1) {
                    Log::info('Paysera callback: Order already processed', [
                        'orderid' => $orderId,
                        'current_status' => $order->order_status
                    ]);
                    return response('OK', 200);
                }

                // Сумма должна совпадать с тем, что уходит в Paysera из checkout/pay()
                $testMode = (bool) config('payment.paysera.test_mode', false);
                $expectedAmount = $testMode ? 1 : (int) round(((float) $order->total_price) * 100);
                $receivedAmount = isset($response['payamount']) ? (int) $response['payamount'] : (int) $response['amount'];
                
                if ($expectedAmount != $receivedAmount) {
                    Log::error('Paysera callback: Amount mismatch', [
                        'orderid' => $orderId,
                        'expected' => $expectedAmount,
                        'received' => $receivedAmount
                    ]);
                    
                    // Audit log для несоответствия суммы
                    Audit::audit(
                        'error',
                        'Order',
                        'Payment Amount Mismatch',
                        $orderId,
                        "Paysera maksājuma summa nesakrīt. Sagaidītā: {$expectedAmount}, Saņemtā: {$receivedAmount}",
                        Order::find($orderId)
                    );
                    
                    return response('Amount mismatch', 400);
                }

                // Генерируем order_number если его еще нет
                $order_number = $order->order_number;
                
                if (is_null($order_number)) {
                    // Используем блокировку для предотвращения дублирования номеров
                    DB::statement('SELECT GET_LOCK("order_number_generation", 10)');
                    
                    try {
                        // Находим максимальный order_number и добавляем 1
                        $maxOrderNumber = DB::table('orders_')
                            ->whereNotNull('order_number')
                            ->max('order_number');
                        
                        $order_number = ($maxOrderNumber ?? 0) + 1;
                        
                        // Проверяем, что номер действительно уникальный
                        while (DB::table('orders_')->where('order_number', $order_number)->exists()) {
                            $order_number++;
                        }
                    } finally {
                        // Освобождаем блокировку
                        DB::statement('SELECT RELEASE_LOCK("order_number_generation")');
                    }
                }

                // Обновляем заказ в базе данных
                DB::table('orders_')->where('id', $orderId)->update([
                    'order_status' => 2, // Оплачен
                    'order_number' => $order_number,
                    'payment_status' => 'paid',
                    'paysera_transaction_id' => $response['requestid'] ?? null,
                    'paysera_callback_data' => json_encode($response),
                    'updated_at' => now()
                ]);

                // Применяем промокод, если есть
                if (isset($order->promo_code) && !is_null($order->promo_code)) {
                    $promo = Promo::where('code', $order->promo_code)->first();
                    if ($promo) {
                        $promo->used++;
                        $promo->save();
                    }
                }

                // Очищаем корзину после успешной оплаты
                \App\Http\Controllers\ShopController::emptyCart();

                Log::info('Paysera callback: Order successfully processed', [
                    'orderid' => $orderId,
                    'order_number' => $order_number,
                    'transaction_id' => $response['requestid'] ?? null
                ]);

                // Audit log для успешной оплаты
                $updatedOrder = Order::find($orderId);
                Audit::audit(
                    'info',
                    'Order',
                    'Payment Successful',
                    $orderId,
                    "Paysera maksājums veiksmīgs. Pasūtījuma Nr: {$order_number}, Transakcijas ID: " . ($response['requestid'] ?? 'N/A'),
                    $updatedOrder
                );

                // Письмо из callback; при сбое повторит shop_done (accepturl)
                try {
                    if ($updatedOrder && !empty($updatedOrder->email)) {
                        $sent = $emailService->sendOrderConfirmation($updatedOrder);

                        Audit::audit(
                            $sent ? 'info' : 'warning',
                            'Order',
                            'Order Email',
                            $orderId,
                            $sent
                                ? "Order email sent (Paysera callback). Pasūtījuma Nr: {$order_number}"
                                : "Order email FAILED (Paysera callback). Pasūtījuma Nr: {$order_number}",
                            $updatedOrder
                        );
                    } else {
                        Audit::audit(
                            'warning',
                            'Order',
                            'Order Email',
                            $orderId,
                            "Order email skipped: missing customer email (Paysera callback). Pasūtījuma Nr: {$order_number}",
                            $updatedOrder
                        );
                    }
                } catch (\Exception $e) {
                    Log::error('Paysera callback: Email sending failed: ' . $e->getMessage(), [
                        'orderid' => $orderId,
                        'order_number' => $order_number,
                        'exception' => get_class($e),
                    ]);
                    Audit::audit(
                        'warning',
                        'Order',
                        'Order Email',
                        $orderId,
                        "Order email exception (Paysera callback): " . $e->getMessage(),
                        $updatedOrder
                    );
                }

                // Возвращаем "OK" для Paysera
                return response('OK', 200);
                
            } else {
                // Платеж не "успешный" (status != 1), но это не всегда ошибка.
                // status = 2 - платеж принят, но не подтвержден
                // status = 3 - дополнительная информация
                $status = $response['status'] ?? 'unknown';
                $orderId = $response['orderid'] ?? null;

                if ($status === '2' || $status === 2) {
                    Log::info('Paysera callback: Payment pending/unconfirmed', [
                        'orderid' => $orderId ?? 'unknown',
                        'status' => $status,
                    ]);

                    if ($orderId !== null) {
                        Audit::audit(
                            'info',
                            'Order',
                            'Payment Pending',
                            $orderId,
                            "Paysera maksājums pieņemts, bet nav apstiprināts. Statuss: {$status}",
                            Order::find($orderId)
                        );
                    }

                    return response('OK', 200);
                }

                if ($status === '3' || $status === 3) {
                    Log::info('Paysera callback: Additional payment information', [
                        'orderid' => $orderId ?? 'unknown',
                        'status' => $status,
                    ]);

                    if ($orderId !== null) {
                        Audit::audit(
                            'info',
                            'Order',
                            'Payment Info',
                            $orderId,
                            "Paysera maksājums: papildinformācija. Statuss: {$status}",
                            Order::find($orderId)
                        );
                    }

                    return response('OK', 200);
                }

                Log::warning('Paysera callback: Payment not successful', [
                    'orderid' => $orderId ?? 'unknown',
                    'status' => $status,
                    'response' => $response
                ]);

                // Audit log для действительно неуспешной оплаты
                if ($orderId !== null) {
                    Audit::audit(
                        'warning',
                        'Order',
                        'Payment Failed',
                        $orderId,
                        "Paysera maksājums neizdevās. Statuss: {$status}",
                        Order::find($orderId)
                    );
                }

                // Для Paysera лучше всегда отвечать 200 OK на валидный callback, чтобы не было бесконечных повторов.
                return response('OK', 200);
            }
            
        } catch (WebToPayException $e) {
            Log::error('Paysera callback exception: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'request' => $request->all()
            ]);
            
            return response('Error: ' . $e->getMessage(), 400);
            
        } catch (\Exception $e) {
            Log::error('Paysera callback unexpected error: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response('Unexpected error', 500);
        }
    }
}


