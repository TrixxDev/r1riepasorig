<?php

namespace App\Http\Controllers;

use App\Helper\Utility;
use App\Http\Controllers\EmailController as Mailer;
use App\Models\Promo;
use App\Paysera\WebToPay;
use App\Services\CartService;
use App\Services\PriceCalculationService;
use App\Services\ShippingService;
use App\Services\EmailService;
use App\Services\WhatsappErrorLogger;
use App\Services\WhatsappNotificationAudit;
use App\Services\WhatsappParallelSender;
use App\Services\Marketing\GoogleAdsConversionService;
use App\Services\Marketing\PurchaseTrackingService;
use App\Events\CartUpdated;
use Carbon\Carbon;
use http\Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ShopController extends Controller
{
    protected $cart;
    protected $cartService;
    protected $priceCalculationService;
    protected $shippingService;
    protected $emailService;

    /**
     * Custom logger for order tracking
     */
    private function logOrder($message, $context = [])
    {
        $logMessage = '[' . now() . '] ' . $message;
        if (!empty($context)) {
            $logMessage .= ' | Context: ' . json_encode($context);
        }
        
        // Записываем в отдельный файл для админки
        file_put_contents(
            storage_path('logs/order_tracking.log'),
            $logMessage . PHP_EOL,
            FILE_APPEND | LOCK_EX
        );
    }

    protected static function resolveGuestSessionId(): string
    {
        $sessionId = Cookie::get('persistent_session_id');

        if (is_string($sessionId) && $sessionId !== '') {
            return $sessionId;
        }

        $sessionId = session()->getId();

        if (!is_string($sessionId) || $sessionId === '') {
            $sessionId = Str::uuid()->toString();
        }

        Cookie::queue('persistent_session_id', $sessionId, 43200);

        return $sessionId;
    }

    public float $radiusBorder = 360.7;
    /**
     * @deprecated Recipients use config('services.whatsapp_parallel.wpp_group_order'). Kept for backward compatibility if referenced elsewhere.
     */
    public string $orderWpp = '120363248805017034@g.us';
    public ?int $promo_value = null;

    public function __construct(
        CartService $cartService, 
        PriceCalculationService $priceCalculationService, 
        ShippingService $shippingService,
        EmailService $emailService
    ) {
        $this->middleware('checkcart');
        $this->cartService = $cartService;
        $this->priceCalculationService = $priceCalculationService;
        $this->shippingService = $shippingService;
        $this->emailService = $emailService;
    }

    protected static function getSelfUrl(): string
    {
        $url = substr(strtolower($_SERVER['SERVER_PROTOCOL']), 0, strpos($_SERVER['SERVER_PROTOCOL'], '/'));

        if (isset($_SERVER['HTTPS']) === true) {
            $url .= ($_SERVER['HTTPS'] === 'on') ? 's' : '';
        }

        $url .= '://' . $_SERVER['HTTP_HOST'];

        if (isset($_SERVER['SERVER_PORT']) === true && $_SERVER['SERVER_PORT'] !== '80') {
            $url .= ':' . $_SERVER['SERVER_PORT'];
        }

        $url .= dirname($_SERVER['SCRIPT_NAME']);

        return $url;
    }

    public static function options(): array
    {
        return ShippingService::getOptions();
    }

    /**
     * Получить текущий незавершенный заказ пользователя
     *
     * @return object|null
     */
    protected function getCurrentOrder()
    {
        // Check if the user is logged in
        $userId = Auth::id();
        // Get the current session ID (instead of using a cookie)
        $sessionId = $userId ? null : Cookie::get('persistent_session_id');

        return DB::table('orders_')
            ->where(function ($query) use ($sessionId, $userId) {
                if ($userId) {
                    $query->where('user_id', $userId);
                } else {
                    $query->where('session_id', $sessionId);
                }
            })
            ->where('order_status', 1) // Only pending orders
            ->first();
    }

    public function index(Request $request)
    {
        $cart = session()->get('cart', ['products' => []]);

        $totalSum = 0;
        foreach ($cart['products'] as $product) {
            $totalSum += $product['quantity'] * $product['price'];
        }

        if ($request->isMethod('post')) {
            // Retrieve promo code information
            $promo_code = $request->data['promo_code'] ?? null;
            $promo_type = $promo_value = null;

            if (!is_null($promo_code)) {
                $promo = Promo::where('code', $promo_code)->first();
                if ($promo) {
                    $promo_type = $promo->status == 1 ? 'percentage' : 'fixed';
                    $promo_value = $promo->value;
                }
            }

            // Retrieve delivery or mounting options from request
            $delivery_method = 1; // По умолчанию 1 - самовывоз
            $mounting_office = null;
            $delivery_city = null;
            $delivery_price = null;
            $delivery_address = null;
            $delivery_door_code = null;
            $mounting_price = $request->fitting_price ?? null;

            // Проверяем, передается ли значение cart_delivery_radio
            if (isset($request->data['cart_delivery_radio'])) {
                $delivery_option = $request->data['cart_delivery_radio'];
                // Если выбрана опция доставки (значение 3)
                if ($delivery_option == 3) {
                    $delivery_method = 2; // 2 - доставка
                    if (isset($request->data['shipping_city'])) {
                        $delivery_city = $request->data['shipping_city'];
                    }
                    $delivery_price = $request->delivery_price;
                    if (isset($request->data['shipping_address'])) {
                        $delivery_address = $request->data['shipping_address'];
                    }
                    if (isset($request->data['door_code'])) {
                        $delivery_door_code = $request->data['door_code'];
                    }
                } else {
                    // Если выбран самовывоз (значение 1 или 2)
                    $mounting_office = $delivery_option;
                }
            }

            // Рассчитываем итоговую сумму с учетом всех параметров
            $finalTotalPrice = $this->priceCalculationService->calculateTotalPrice(
                $totalSum, 
                $promo_type, 
                $promo_value, 
                $delivery_price, 
                $mounting_price
            );

            $existingOrder = $this->getCurrentOrder();

            if ($existingOrder) {
                // Update existing order
                $oldOrder = \App\Models\Order::find($existingOrder->id);
                
                DB::table('orders_')->where('id', $existingOrder->id)->update([
                    'order_details' => json_encode($cart),
                    'promo_code' => $promo_code,
                    'discount_type' => $promo_type,
                    'discount_value' => $promo_value,
                    'total_price' => $finalTotalPrice,
                    'delivery_method' => $delivery_method,
                    'delivery_city' => $delivery_city,
                    'delivery_price' => $delivery_price,
                    'delivery_address' => $delivery_address,
                    'door_code' => $delivery_door_code,
                    'mounting_office' => $mounting_office,
                    'mounting_price' => $mounting_price,
                    'delete_at' => date('Y-m-d', strtotime('+1 month')),
                    'updated_at' => now(),
                ]);

                $orderId = $existingOrder->id;
                
                // Audit log for order update
                $identifier = $existingOrder->email ?: $existingOrder->customer_name ?: "Order #{$orderId}";
                \App\Models\Audit::audit('info', 'Order', 'Cart Update', $orderId, 'Pasūtījuma groza dati atjaunināti - ' . $identifier, $oldOrder);
            } else {
                // Create a new order if no existing order found
                $orderData = [
                    'user_id' => Auth::id(),
                    'session_id' => Auth::id() ? null : Cookie::get('persistent_session_id'),
                    'order_details' => json_encode($cart),
                    'promo_code' => (isset($promo)) ? $promo_code : '',
                    'discount_type' => $promo_type,
                    'discount_value' => $promo_value,
                    'total_price' => $finalTotalPrice,
                    'delivery_method' => $delivery_method,
                    'delivery_city' => $delivery_city,
                    'delivery_price' => $delivery_price,
                    'delivery_address' => $delivery_address,
                    'door_code' => $delivery_door_code,
                    'mounting_office' => $mounting_office,
                    'mounting_price' => $mounting_price,
                    'delete_at' => date('Y-m-d', strtotime('+1 month')),
                    'created_at' => now(),
                    'updated_at' => now(),
                    'order_status' => 1, // Initial status
                ];

                // Save new order in the database
                $orderId = DB::table('orders_')->insertGetId($orderData);
                
                // Audit log for new order creation
                $identifier = $request->data['email'] ?? $request->data['customer_name'] ?? "Order #{$orderId}";
                $newOrder = \App\Models\Order::find($orderId);
                \App\Models\Audit::audit('info', 'Order', 'Order Creation', $orderId, 'Jauns pasūtījums izveidots - ' . $identifier, $newOrder);
            }

            // Redirect to the next step (user credentials)
            return redirect()->route('shop.credentials');
        }

        $existingOrder = $this->getCurrentOrder();

	//dd($totalSum, $existingOrder);

        return view('shop.home', compact('totalSum', 'existingOrder'));
    }

    public function credentials(Request $request)
    {
        $existingOrder = $this->getCurrentOrder();

        if ($request->isMethod('post')) {

            $data = (object) $request->data;

	    $rawPhoneNumber = $data->phone_number ?? null;

            // Нормализация телефона: удаляем код страны из номера, пробелы, скобки и прочие символы
            if (isset($data->phone_number)) {
                $rawNumber = (string) $data->phone_number;
                $countryCode = (string) ($data->phone_country_code ?? '');
                $digits = preg_replace('/\D+/', '', $rawNumber);
                $countryDigits = preg_replace('/\D+/', '', $countryCode);

                if (!empty($countryDigits) && substr($digits, 0, strlen($countryDigits)) === $countryDigits) {
                    $digits = substr($digits, strlen($countryDigits));
                }

                // Для LV храним ровно 8 цифр (если прислали длиннее с кодом)
                if ($countryDigits === '371' && strlen($digits) > 8) {
                    $digits = substr($digits, -8);
                }

                $data->phone_number = $digits;
            }

            // Базовая валидация для email и телефона
            $validationRules = [
                'data.email' => 'required|email:rfc,dns',
            ];
            
            $validationMessages = [
                'data.email.required' => 'E-pasts ir obligāts',
                'data.email.email' => 'Lūdzu ievadiet derīgu e-pasta adresi (piemēram: email@domain.com)',
            ];
            
            // Проверка номера телефона для Латвии
            if (isset($data->phone_country_code) && $data->phone_country_code === '+371') {
                // Для Латвии проверяем, что номер содержит 8 цифр
                $validationRules['data.phone_number'] = 'required|regex:/^[0-9]{8}$/';
                $validationMessages['data.phone_number.required'] = 'Tālruņa numurs ir obligāts';
                $validationMessages['data.phone_number.regex'] = 'Latvijas tālruņa numuram jāsastāv no 8 cipariem';
            } else {
                // Для других стран - просто проверка на наличие
                $validationRules['data.phone_number'] = 'required';
                $validationMessages['data.phone_number.required'] = 'Tālruņa numurs ir obligāts';
            }
            
            // Применяем валидацию
            $validator = Validator::make($request->all(), $validationRules, $validationMessages);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

	    $this->logOrder('Credentials submitted', [
                'order_id' => $existingOrder ? $existingOrder->id : null,
                'raw_phone_number' => $rawPhoneNumber,
                'normalized_phone_number' => $data->phone_number ?? null,
                'phone_country_code' => $data->phone_country_code ?? null,
                'customer_email' => $data->email ?? null,
            ]);

            $carPlate = trim((string) ($data->car_plate ?? '')) ?: null;
            $carBrand = trim((string) ($data->car_brand ?? '')) ?: null;
            $carModel = trim((string) ($data->car_model ?? '')) ?: null;
            $carReleaseYear = trim((string) ($data->car_release_year ?? '')) ?: null;
            $carEngineSize = trim((string) ($data->car_engine_size ?? '')) ?: null;

            if ($carPlate === null && $carBrand === null && $carModel === null && $carReleaseYear === null && $carEngineSize === null) {
                $car_details = null;
            } else {
                $car_details = json_encode([
                    'car_plate' => $carPlate,
                    'car_brand' => $carBrand,
                    'car_model' => $carModel,
                    'car_release_year' => $carReleaseYear,
                    'car_engine_size' => $carEngineSize
                ]);
            }
            
            $attributes = [
                'customer_name' => $data->name,
                'customer_surname' => $data->surname,
                'email' => $data->email,
                'phone_country_code' => $data->phone_country_code,
                'phone_number' => $data->phone_number,
                'company_reg_nr' => $data->company_registration_number ?? null,
                'company_pvn_nr' => $data->company_pvn_number ?? null,
                'company_name' => $data->company_name ?? null,
                'company_address' => $data->company_address ?? null,
                'comments' => $data->notes,
                'car_details' => $car_details,
                'email_notification' => $data->email_notifications ?? 0,
            ];

            $oldOrder = \App\Models\Order::find($existingOrder->id);
            
            $updated = DB::table('orders_')
                ->where('id', $existingOrder->id) // Assuming 'id' is the primary key
                ->update($attributes);
                
            // Audit log for customer data update
            $identifier = $data->email ?: $data->name ?: "Order #{$existingOrder->id}";
            \App\Models\Audit::audit('info', 'Order', 'Customer Update', $existingOrder->id, 'Klienta dati atjaunināti - ' . $identifier, $oldOrder);

            return redirect()->route('shop.checkout');
        }

        return view('shop.order', compact('existingOrder'));
    }

    public function checkout(Request $request)
    {
        $existingOrder = $this->getCurrentOrder();

        $categories = [];
        $availabilities = [];

        $cart = Utility::decode_info($existingOrder->order_details);

        foreach ($cart->products as $item) {
            $category = str_replace('App\\Models\\', '', $item->category);
            array_push($categories, $category);
            array_push($availabilities, $item->availability);
        }

        $categories = array_unique($categories);
        $availabilities = array_unique($availabilities);

        // total_price уже включает промокод, доставку и монтаж (см. calculateTotalPrice)
        $finalPrice = (float) $existingOrder->total_price;

        if ($request->method() == 'POST') {
            $this->logOrder('Checkout POST request received', [
                'pay' => $request->has('pay'),
                'end' => $request->has('end'),
                'payment' => $request->payment,
                'existing_order_id' => $existingOrder->id ?? 'null'
            ]);

            $order_id = $existingOrder->id;

            // Проверяем способ оплаты
            $payment_method = (int)$request->payment;
            
            // Если выбрана онлайн оплата (Paysera) и нажата кнопка "Apmaksāt"
            if (isset($request->pay) && $payment_method === 3) {
                $this->logOrder('Redirecting to Paysera payment', [
                    'order_id' => $order_id,
                    'amount' => $finalPrice
                ]);
                
		DB::transaction(function () use ($order_id, $payment_method) {
                    $order = DB::table('orders_')
                        ->where('id', $order_id)
                        ->lockForUpdate()
                        ->first();
                    
                    if (!$order) {
                        throw new \Exception('Order not found');
                    }
                    
                    // Разрешаем изменение payment_method для незавершенных заказов (order_status = 1)
                    // Это позволяет пользователю выбрать Paysera даже если ранее был выбран другой способ
                    $canChangePaymentMethod = ($order->order_status == 1);
                    
                    if ($order->payment_method !== null && $order->payment_method != $payment_method) {
                        if ($canChangePaymentMethod) {
                            // Разрешаем изменение для незавершенных заказов
                            $this->logOrder('Changing payment_method from ' . $order->payment_method . ' to ' . $payment_method . ' for order_id: ' . $order_id . ' (order_status = 1, allowed)');
                            DB::table('orders_')
                                ->where('id', $order_id)
                                ->update(['payment_method' => $payment_method]);
                        } else {
                            // Запрещаем изменение для завершенных заказов
                            $this->logOrder('WARNING: Cannot change payment_method from ' . $order->payment_method . ' to ' . $payment_method . ' for order_id: ' . $order_id . ' - order_status = ' . $order->order_status);
                            throw new \Exception('Cannot change payment method for completed order');
                        }
                    } else if ($order->payment_method === null) {
                        // Атомарное обновление только если payment_method еще не установлен
                        $updated = DB::table('orders_')
                            ->where('id', $order_id)
                            ->whereNull('payment_method')
                            ->update(['payment_method' => $payment_method]);
                        
                        if ($updated === 0) {
                            $this->logOrder('Race condition: payment_method was set by another request for order_id: ' . $order_id);
                        }
                    }
                });

                // Формируем данные для оплаты через Paysera
                $testMode = (bool) config('payment.paysera.test_mode', false);
                $amount = $testMode ? 1 : (int) round($finalPrice * 100);
                
                $data = [
                    'order_id' => $order_id, 
                    'amount' => $amount,
                    'email' => $existingOrder->email
                ];
                
                // Редиректим на Paysera
                return $this->pay($data);
            } 
            // Для других способов оплаты продолжаем обычным путем
            else if (isset($request->pay) || isset($request->end)) {
                $this->logOrder('Processing standard payment method', [
                    'payment_method' => $payment_method
                ]);
                return $this->end($payment_method);
            }
        }

        return view('shop.checkout', compact('existingOrder', 'finalPrice', 'categories', 'availabilities'));
    }

    public function pay($data)
    {
        $order_id = $data['order_id'];

        try {
            // Получаем конфигурацию Paysera
            // Временно хардкодим, пока не разберемся с конфигом
            $projectId = config('payment.paysera.project_id') ?? '209872';
            $signPassword = config('payment.paysera.sign_password') ?? 'ef3e86e4902558e3779ecc84d72a6d8c';
            $currency = config('payment.paysera.currency') ?? 'EUR';
            $country = config('payment.paysera.country') ?? 'LV';
            $testMode = (bool) config('payment.paysera.test_mode', false);
            
            $this->logOrder('Preparing Paysera payment', [
                'order_id' => $order_id,
                'amount' => $data['amount'],
                'email' => $data['email'],
                'project_id' => $projectId,
                'has_password' => !empty($signPassword),
                'test_mode' => $testMode
            ]);
            
            // Проверяем, что параметры загружены
            if (empty($projectId) || empty($signPassword)) {
                throw new \Exception('Paysera configuration is missing. Check config/payment.php');
            }
            
            // Формируем параметры для Paysera
            $payseraParams = [
                'projectid' => $projectId,
                'sign_password' => $signPassword,
                'orderid' => $data['order_id'],
                'amount' => $data['amount'],
                'p_email' => $data['email'],
                'currency' => $currency,
                'country' => $country,
                'accepturl' => route('shop.success', $order_id),
                'cancelurl' => route('shop.checkout'),
                'callbackurl' => url('/callback'),
                'paytext' => 'Apmaksa par pasūtījumu Nr.' . $order_id,
            ];
            
            // Добавляем параметр test только если НЕ тестовый режим (для боевого режима)
            if (!$testMode) {
                $payseraParams['test'] = 0; // Боевой режим
            }
            
            // Редирект на Paysera для оплаты
            WebToPay::redirectToPayment($payseraParams);
        } catch (\Exception $exception) {
            $this->logOrder('Paysera payment error: ' . $exception->getMessage(), [
                'order_id' => $order_id,
                'exception' => get_class($exception)
            ]);
            return redirect()->route('shop.checkout')->with('error', 'Kļūda veicot maksājumu: ' . $exception->getMessage());
        }
    }

    public function end($payment_method = null) {
        // Используем переданный payment_method, если он не передан - берем из request
        if ($payment_method === null) {
            $payment_method = (int)request()->payment;
        } else {
            $payment_method = (int)$payment_method;
        }
        
        // Используем транзакцию с блокировкой строки для предотвращения race condition
        return DB::transaction(function () use ($payment_method) {
            // Получаем заказ с блокировкой строки (SELECT FOR UPDATE)
            $userId = Auth::id();
            $sessionId = $userId ? null : self::resolveGuestSessionId();
            
            $order = DB::table('orders_')
                ->where(function ($query) use ($sessionId, $userId) {
                    if ($userId) {
                        $query->where('user_id', $userId);
                    } else {
                        $query->where('session_id', $sessionId);
                    }
                })
                ->where('order_status', 1)
                ->lockForUpdate() // Блокируем строку для чтения
                ->first();
            
            // Проверяем, что заказ найден
            if (!$order) {
                $this->logOrder('ERROR: Order not found in end() method');
                return redirect()->route('pieraksts')->with('error', 'Pasūtījums nav atrasts');
            }
            
            $oldOrder = \App\Models\Order::find($order->id);
            
            // Проверяем текущий payment_method после блокировки
            $currentPaymentMethod = $order->payment_method;
            $orderStatus = $order->order_status;
            
            // Разрешаем изменение payment_method только для незавершенных заказов (order_status = 1)
            // Это позволяет пользователю изменить способ оплаты, если он отменил Paysera или выбрал неправильный способ
            $canChangePaymentMethod = ($orderStatus == 1); // Только для незавершенных заказов
            
            if ($currentPaymentMethod !== null && $currentPaymentMethod != $payment_method) {
                if ($canChangePaymentMethod) {
                    // Разрешаем изменение для незавершенных заказов
                    $this->logOrder('Changing payment_method from ' . $currentPaymentMethod . ' to ' . $payment_method . ' for order_id: ' . $order->id . ' (order_status = 1, allowed)');
                    
                    // Обновляем payment_method
                    DB::table('orders_')
                        ->where('id', $order->id)
                        ->update([
                            'payment_method' => $payment_method,
                        ]);
                    
                    $this->logOrder('Updated payment_method to ' . $payment_method . ' for order_id: ' . $order->id);
                } else {
                    // Запрещаем изменение для завершенных заказов
                    $this->logOrder('WARNING: Cannot change payment_method from ' . $currentPaymentMethod . ' to ' . $payment_method . ' for order_id: ' . $order->id . ' - order_status = ' . $orderStatus . ' (not allowed)');
                    $payment_method = $currentPaymentMethod;
                }
            } else if ($currentPaymentMethod === null) {
                // Атомарное обновление только если payment_method еще не установлен
                $updated = DB::table('orders_')
                    ->where('id', $order->id)
                    ->whereNull('payment_method') // Условие для атомарности
                    ->update([
                        'payment_method' => $payment_method,
                    ]);
                
                if ($updated === 0) {
                    // Кто-то уже установил payment_method между блокировкой и обновлением
                    // Читаем актуальное значение
                    $order = DB::table('orders_')->where('id', $order->id)->first();
                    $payment_method = $order->payment_method;
                    $this->logOrder('Race condition detected: payment_method was set by another request. Using existing value: ' . $payment_method . ' for order_id: ' . $order->id);
                } else {
                    $this->logOrder('Updated payment_method to ' . $payment_method . ' for order_id: ' . $order->id);
                }
            } else {
                $this->logOrder('payment_method already set to ' . $currentPaymentMethod . ' for order_id: ' . $order->id);
            }
            
            // Обновляем order для использования после транзакции
            $order = DB::table('orders_')->where('id', $order->id)->first();
            
            // Audit log for order completion
            $identifier = $order->email ?: $order->customer_name ?: "Order #{$order->id}";
            \App\Models\Audit::audit('info', 'Order', 'Order Completion', $order->id, "Pasūtījums pabeigts #{$order->id} - {$identifier}", $oldOrder);

        Session::remove('cart');
        Cookie::queue(Cookie::make('cart', null, -1));
        Self::emptyCart();

        // Генерируем order_number если его еще нет
        $orderWithNumber = DB::table('orders_')->where('id', $order->id)->first();
        $order_number = $orderWithNumber->order_number;
        
        $this->logOrder('Current order_number before generation: ' . ($order_number ?? 'NULL') . ' for order_id: ' . $order->id);
        
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
                
                // Обновляем заказ с новым order_number
                DB::table('orders_')->where('id', $order->id)->update(['order_number' => $order_number]);
                
                $this->logOrder('Generated new order_number: ' . $order_number . ' for order_id: ' . $order->id);
            } finally {
                // Освобождаем блокировку
                DB::statement('SELECT RELEASE_LOCK("order_number_generation")');
            }
        } else {
            $this->logOrder('Using existing order_number: ' . $order_number . ' for order_id: ' . $order->id);
        }
        
        $this->logOrder('Redirecting to shop.done with order_number: ' . $order_number);
        return Redirect::route('shop.done', $order_number);
        });
    }

    public function shop_success($id) {
        // Получаем order_number для редиректа
        $order = DB::table('orders_')->where('id', $id)->first();
        $order_number = $order->order_number ?? $id; // Fallback на id если order_number NULL
        
        return Redirect::route('shop.done', $order_number);
    }

    /**
     * Internet shop order desk: TextMeBot + optional parallel WhatsApp API (same config as slot bookings).
     */
    protected function notifyOrderDeskWhatsApp($order, int $finalOrderNumber): void
    {
        $fmtDate = now()->format('d.m.Y');
        $time = now()->format('H:i');
        $phone = trim((string) ($order->phone_country_code ?? '').(string) ($order->phone_number ?? ''));
        $commentPlain = (! empty($order->comments))
            ? ', '.preg_replace("/\s+/u", ' ', (string) $order->comments)
            : '';
        $plain = 'Jauns pasūtījums - Nr. '.$finalOrderNumber.', '.$fmtDate.' '.$time.', '.$phone.$commentPlain;

        $orderJid = (string) config('services.whatsapp_parallel.wpp_group_order', '120363248805017034@g.us');

        try {
            $url = 'http://api.textmebot.com/send.php?recipient='.$orderJid.'&apikey=d6nsRWNp1xpc&text='.rawurlencode($plain);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_exec($ch);
            curl_close($ch);

            $this->logOrder('WhatsApp notification sent', [
                'order_number' => $finalOrderNumber,
            ]);
        } catch (\Exception $e) {
            $this->logOrder('WhatsApp notification failed: '.$e->getMessage());
            WhatsappErrorLogger::exception($e, 'ShopController notifyOrderDeskWhatsApp: TextMeBot curl', [
                'order_id' => $order->id ?? null,
                'order_number' => $finalOrderNumber,
            ]);
        }

        $parallelHttpResult = null;
        $parallelTestToMissing = false;
        if (WhatsappParallelSender::isParallelApiEnabled()) {
            $wppParallelCfg = config('services.whatsapp_parallel', []);
            $testMode = WhatsappParallelSender::isTestMode();
            $testTo = WhatsappParallelSender::testToStringFromConfig($wppParallelCfg['test_to'] ?? null);
            $to = $testMode ? $testTo : $orderJid;
            if ($to === '') {
                $parallelTestToMissing = $testMode;
                $this->logOrder('Whatsapp parallel skipped: WHATSAPP_PARALLEL_TEST_TO empty in test mode');
                WhatsappErrorLogger::write('warning', 'ShopController: WHATSAPP_PARALLEL_TEST_TO empty while test mode on', [
                    'order_id' => $order->id ?? null,
                    'order_number' => $finalOrderNumber,
                ]);
            } else {
                $parallelHttpResult = (new WhatsappParallelSender)->send($to, $plain);
            }
        }

        WhatsappNotificationAudit::digestsForOrder(
            $order,
            (int) $order->id,
            (string) $finalOrderNumber,
            $parallelHttpResult,
            $parallelTestToMissing
        );
    }

    public function shop_done($order_number, PurchaseTrackingService $purchaseTracking,
    GoogleAdsConversionService $googleAds) {
        $this->logOrder('shop_done called with order_number: ' . $order_number);
        
        // Сначала пытаемся найти по order_number
        $order = DB::table('orders_')
            ->where('order_number', $order_number)
            ->first();
            
        // Если не найден по order_number, возможно это старый заказ с NULL order_number
        // В таком случае ищем по id (если передан числовой параметр)
        if (!$order && is_numeric($order_number)) {
            $order = DB::table('orders_')
                ->where('id', $order_number)
                ->first();
        }

        if (!$order) {
            $this->logOrder('ERROR: Order not found in shop_done with order_number: ' . $order_number);
            return redirect()->route('pieraksts');
        }
        
        if (!in_array($order->order_status, [1, 2], true)) {
            $this->logOrder('ERROR: Order status is not allowed in shop_done. Status: ' . $order->order_status . ', order_number: ' . $order_number);
            return redirect()->route('pieraksts');
        }

        if ((int)$order->order_status === 2) {
            $this->logOrder('Order already marked as completed before shop_done. Retrying confirmation email if needed.', [
                'order_number' => $order_number,
                'order_id' => $order->id
            ]);

            $orderModel = \App\Models\Order::find($order->id);
            if ($orderModel) {
                $sent = $this->emailService->sendOrderConfirmation($orderModel);
                $this->logOrder($sent ? 'Confirmation email sent from shop_done (retry)' : 'Confirmation email not sent from shop_done (retry)', [
                    'order_id' => $order->id,
                    'email' => $order->email,
                ]);
            }

            $order = DB::table('orders_')->where('id', $order->id)->first();
            $purchaseTracking->dispatchServerPurchase($order, request());

            return view('shop.done', [
                'order_number' => $order->order_number ?? $order_number,
                'marketingPurchase' => $purchaseTracking->buildClientPayload($order),
            ]);
        }

        // Убеждаемся, что order_number сгенерирован (на случай, если callback Paysera ещё не успел)
        if (is_null($order->order_number)) {
            DB::statement('SELECT GET_LOCK("order_number_generation", 10)');
            try {
                // Повторно читаем order_number внутри блокировки, вдруг он уже проставлен callback'ом
                $existingNumber = DB::table('orders_')
                    ->where('id', $order->id)
                    ->value('order_number');

                if (is_null($existingNumber)) {
                    $maxOrderNumber = DB::table('orders_')
                        ->whereNotNull('order_number')
                        ->max('order_number');

                    $generatedOrderNumber = ($maxOrderNumber ?? 0) + 1;

                    while (DB::table('orders_')->where('order_number', $generatedOrderNumber)->exists()) {
                        $generatedOrderNumber++;
                    }

                    DB::table('orders_')->where('id', $order->id)->update(['order_number' => $generatedOrderNumber]);
                    $order->order_number = $generatedOrderNumber;
                    $this->logOrder('Generated order_number in shop_done: ' . $generatedOrderNumber . ' for order_id: ' . $order->id);
                } else {
                    $order->order_number = $existingNumber;
                    $this->logOrder('Using order_number from DB in shop_done: ' . $existingNumber . ' for order_id: ' . $order->id);
                }
            } finally {
                DB::statement('SELECT RELEASE_LOCK("order_number_generation")');
            }
        }

        $final_order_number = $order->order_number;

        $attributes = [
            'order_number' => $final_order_number,
        ];

        // Обновляем статус только если заказ ещё не отмечен как завершён
        if ((int)$order->order_status !== 2) {
            $attributes['order_status'] = 2;
        }

        $shouldApplyPromo = ((int)$order->order_status !== 2) && isset($order->promo_code) && !is_null($order->promo_code);

        if ($shouldApplyPromo) {
            $promo = Promo::where('code', $order->promo_code)->first();
            if ($promo) {
                $promo->used++;
                $promo->save();
            }
        }

        if (!empty($attributes)) {
            DB::table('orders_')
                ->where('id', $order->id)
                ->update($attributes);
        }

        $this->notifyOrderDeskWhatsApp($order, (int) $final_order_number);

        $orderModel = \App\Models\Order::find($order->id);
        if ($orderModel) {
            $sent = $this->emailService->sendOrderConfirmation($orderModel);
            $this->logOrder($sent ? 'Email sent successfully' : 'Email sending failed', [
                'order_id' => $order->id,
                'email' => $order->email,
            ]);
        }

        // Очищаем корзину после завершения заказа
        Self::emptyCart();

        $this->logOrder('Order completed successfully', [
            'order_id' => $order->id,
            'order_number' => $final_order_number,
            'customer_email' => $order->email
        ]);

        $order = DB::table('orders_')->where('id', $order->id)->first();
        $purchaseTracking->dispatchServerPurchase($order, request());

        return view('shop.done', [
            'order_number' => $final_order_number,
            'marketingPurchase' => $purchaseTracking->buildClientPayload($order),
        ]);
    }

    protected function dispatchGoogleAdsPurchase(GoogleAdsConversionService $googleAds, $order): void
    {
        $phone = '';
        if (isset($order->phone_country_code, $order->phone_number)) {
            $phone = $order->phone_country_code.$order->phone_number;
        }

        $conversionData = [
            'transaction_id'  => (string) ($order->order_number ?? $order->id),
            'value'           => (float) ($order->total_price ?? 0),
            'currency'        => 'EUR',
            'email'           => $order->email ?? null,
            'phone'           => $phone ?: null,
            'gclid'           => $order->gclid ?? null,
            'conversion_time' => now()->format('Y-m-d H:i:sP'),
        ];

        dispatch(function () use ($googleAds, $conversionData) {
            try {
                $googleAds->sendPurchaseConversion($conversionData);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Google Ads purchase conversion error', ['message' => $e->getMessage()]);
            }
        })->afterResponse();
    }

    // Function to update the cart in the database
    public static function updateCartInDatabase($total_sum = 0)
    {
        // Check if the user is logged in
        $userId = Auth::id();
        // Get the current session ID (instead of using a cookie)
        $sessionId = $userId ? null : Cookie::get('persistent_session_id');

        // Retrieve the cart using either the user ID or the session ID
        $cart = DB::table('carts')
            ->where(function ($query) use ($userId, $sessionId) {
                if ($userId) {
                    $query->where('user_id', $userId);
                } else {
                    $query->where('session_id', $sessionId);
                }
            })
            ->first();

        if (!$cart) {
            // Insert a new cart if it doesn't exist
            DB::table('carts')->insert([
                'user_id' => $userId,
                'session_id' => $sessionId,
                'cart_data' => Cookie::get('cart'),
                'total_sum' => $total_sum,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            DB::table('carts')
                ->where(function ($query) use ($userId, $sessionId) {
                    if ($userId) {
                        $query->where('user_id', $userId);
                    } else {
                        $query->where('session_id', $sessionId);
                    }
                })->update([
                    'cart_data' => Cookie::get('cart'),
                    'total_sum' => $total_sum,
                    'updated_at' => now()
                ]);
        }
    }

    public static function emptyCart()
    {
        session()->forget('cart');
        Cookie::queue(Cookie::make('cart', null, -1));
        
        event(new CartUpdated());
    }

    /** GET: empty shop cart (replaces legacy /cart/empty + \\Cart::destroy). */
    public function clearCartRedirect(Request $request)
    {
        $payload = json_encode(['products' => [], 'total_sum' => 0]);
        session()->forget('cart');
        Cookie::queue('cart', $payload, 43200);
        self::updateCartInDatabase(0);
        event(new CartUpdated());

        return redirect()->back();
    }

    public function checkShipping(Request $request)
    {
        $totalSum = $request->total_price;

        // Подсчет количества товаров по категориям
        $modelCount = [
            'Autotire' => 0,
            'Moto' => 0,
            'Quadr' => 0,
            'Bigtire' => 0,
        ];

        // Устанавливаем флаг для доставки
        session()->put('cartOptions.fitting', 0);
        session()->put('cartOptions.fitting_price', 0);
	session()->put('cartOptions.shipping', 1);

        // Получаем текущую корзину из сессии
        $cart = session()->get('cart', ['products' => []]);

        // Подсчитываем количество товаров в каждой категории
        foreach ($cart['products'] as $product) {
            $model = $product['category'];

            if (array_key_exists($model, $modelCount)) {
                $modelCount[$model] += $product['quantity'];
            }
        }

        // Находим категорию с наибольшим количеством товаров
        $highestValue = max($modelCount);
        $highestModel = array_search($highestValue, $modelCount);

        // Устанавливаем стоимость доставки на основе количества товаров
        if ($highestValue >= 3 && $highestValue < 5) {
            if ($highestModel == 'Moto') {
                $shippingCost = Self::options()[$highestModel]['shipping'][3];
            } else {
                $shippingCost = Self::options()[$highestModel]['shipping'][4];
            }
        } elseif ($highestValue >= 5) {
            if ($highestModel == 'Moto') {
                $shippingCost = Self::options()[$highestModel]['shipping'][3];
            } else {
                $shippingCost = Self::options()[$highestModel]['shipping'][5];
            }
        } else {
            $shippingCost = Self::options()[$highestModel]['shipping'][$highestValue];
        }

        // Логика для разных городов
        if ($request->input('city') == 1) {
            // Для города с ID 1 (Rīga)
            $shippingCost = Self::options()['shippingDef'];
            
            // Применяем условие бесплатной доставки при сумме заказа >= 115 евро
            if ($totalSum >= 115) {
                $shippingCost = 0;
            }
        } 
        elseif ($request->input('city') == 3) {
            // Для города с ID 3 (Cits) используем расчёт стоимости доставки
            // в зависимости от количества товаров, без возможности бесплатной доставки
            // Стоимость доставки уже рассчитана выше на основе количества товаров
        }

        session()->put('cartOptions.shipping_price', $shippingCost);

        // Возвращаем данные о доставке в формате JSON
        return json_encode(['cartOptions' => session()->get('cartOptions')]);
	//return response()->json([
        //    'cartOptions' => session()->get('cartOptions')
        //]);
    }

    public function checkFitting(Request $request)
    {
        $data = json_decode(json_encode($request->input()));

        session()->put('cartOptions.fitting', 0);
        session()->put('cartOptions.shipping', 0);
        session()->put('cartOptions.shipping_price', 0);

        if ($data->fitting >= 1) {
            // Получаем корзину и проверяем количество и типы товаров
            $cart = session()->get('cart', ['products' => []]);
            $products = $cart['products'];

            // Проверка на количество и однородность товара в корзине
            if (count($products) === 1) {
                $product = reset($products);
                $quantity = $product['quantity'];
                $category = $product['category'];
                $size = $product['d3']; // размер

                // Убедимся, что количество позволяет предложить услугу монтажа
                if (in_array($quantity, [1, 2, 4])) {
                    $suvTire = $this->shippingService->isSuvTire($product['d1'], $product['d2'], $size, $this->radiusBorder);

                    // Рассчитаем цену для услуги монтажа в зависимости от категории и размера
                    $fittingPrice = $this->shippingService->calculateFittingPrice($category, $size, $quantity, $suvTire);
                    session()->put('cartOptions.fitting', 1);
                    session()->put('cartOptions.fitting_price', $fittingPrice);
                } else {
                    session()->put('cartOptions.fitting_price', 0); // Не предлагаем, если количество другое
                }
            } else {
                session()->put('cartOptions.fitting_price', 0); // Если больше одного типа или количества, монтаж не предлагается
            }
        } else {
            session()->put('cartOptions.fitting_price', 0); // Если fitting не выбран
        }

        return json_encode(['cartOptions' => session()->get('cartOptions')]);
    }

    public function ajaxChangeQty(Request $request)
    {
        if ($request->qty <= 0) $request->qty = 1;

        $product_id = $request->input('product_id', $request->input('tire_id'));

        $cart = session()->get('cart', ['products' => []]);

        if ($product_id === null || $product_id === '') {
            $totalSum = 0;
            foreach ($cart['products'] as $product) {
                $totalSum += $product['quantity'] * $product['price'];
            }
            $qtyTotal = array_sum(array_column($cart['products'], 'quantity'));

            return json_encode([
                'total_items' => $qtyTotal,
                'total_sum' => number_format((float) $totalSum, 2, '.', ''),
            ]);
        }

        if (!isset($cart['products'][$product_id])) {
            $totalSum = 0;
            foreach ($cart['products'] as $product) {
                $totalSum += $product['quantity'] * $product['price'];
            }
            $qtyTotal = array_sum(array_column($cart['products'], 'quantity'));

            return json_encode([
                'total_items' => $qtyTotal,
                'total_sum' => number_format((float) $totalSum, 2, '.', ''),
            ]);
        }
        
        $cart['products'][$product_id]['quantity'] = $request->qty;
        if ($request->has('price')) {
            $cart['products'][$product_id]['price'] = $request->price;
        }

        $totalSum = 0;
        foreach ($cart['products'] as $product) {
            $totalSum += $product['quantity'] * $product['price'];
        }

        $cart['total_sum'] = $totalSum;
        session()->put('cart', $cart);
        Cookie::queue('cart', json_encode($cart), 43200);

        Self::updateCartInDatabase($totalSum);
        
        // Audit log for quantity change
        $identifier = Auth::user()->email ?? Cookie::get('persistent_session_id') ?? 'Anonymous';
        \App\Models\Audit::audit('info', 'Cart', 'Quantity Change', $product_id, "Skaits mainīts: {$product_id} ({$request->qty}gab.) - {$identifier}");
        
        event(new CartUpdated());

        // Формируем более полный ответ (total_sum строкой — совместимость с legacy JS .replace(".00",""))
        $qtyTotal = array_sum(array_column($cart['products'], 'quantity'));
        $totalSumFormatted = number_format((float) $totalSum, 2, '.', '');
        $response = [
            'total_items' => $qtyTotal,
            'total_sum' => $totalSumFormatted,
            'subtotal' => $totalSumFormatted,
            'total' => $totalSumFormatted,
            'cart_item' => [
                'id' => $product_id,
                'price' => $cart['products'][$product_id]['price'],
                'quantity' => $request->qty,
                'total' => $cart['products'][$product_id]['price'] * $request->qty
            ],
            'cart_items' => []
        ];

        // Добавляем информацию о каждом товаре в корзине
        foreach ($cart['products'] as $id => $product) {
            $response['cart_items'][$id] = [
                'id' => $id,
                'price' => $product['price'],
                'quantity' => $product['quantity'],
                'total' => $product['price'] * $product['quantity']
            ];
        }

        return json_encode($response);
    }

    public function removeItem($id)
    {
        $cart = session()->get('cart', ['products' => []]);

        if (isset($cart['products'][$id])) {
            unset($cart['products'][$id]);
            session()->put('cart', $cart);

            $totalSum = array_reduce($cart['products'], function ($carry, $product) {
                return $carry + ($product['quantity'] * $product['price']);
            }, 0);

            Cookie::queue('cart', json_encode($cart), 43200);
            Self::updateCartInDatabase($totalSum);
            
            // Audit log for item removal
            $identifier = Auth::user()->email ?? Cookie::get('persistent_session_id') ?? 'Anonymous';
            $productName = $cart['products'][$id]['name'] ?? "ID:{$id}";
            \App\Models\Audit::audit('info', 'Cart', 'Item Removal', $id, "Prece noņemta: {$productName} - {$identifier}");

            event(new CartUpdated());

            if (empty($cart['products'])) {
                Self::emptyCart();
            }
        }

        return redirect()->back();
    }

    public function addToCart(Request $request)
    {
        $tire_id = $request->tire_id;
        $quantity = $request->quantity;

        // Получаем корзину из сессии или создаем новую
        $cart = session()->get('cart', ['products' => [], 'total_sum' => 0]);

        // Если товар уже есть в корзине, обновляем количество
        if (isset($cart['products'][$tire_id])) {
            $cart['products'][$tire_id]['quantity'] += $quantity;
        } else {
            // Иначе добавляем новый товар
            $cart['products'][$tire_id] = [
                'quantity' => $quantity,
                'price' => $request->price
            ];
        }

        // Пересчитываем общую сумму
        $totalSum = 0;
        foreach ($cart['products'] as $product) {
            $totalSum += $product['quantity'] * $product['price'];
        }
        $cart['total_sum'] = $totalSum;

        // Сохраняем корзину в сессию
        session()->put('cart', $cart);

        // Обновляем куки
        Cookie::queue('cart', json_encode($cart), 43200);

        // Обновляем корзину в БД
        Self::updateCartInDatabase($totalSum);
        
        event(new \App\Events\CartUpdated());
        
        // Audit log for adding to cart
        $identifier = Auth::user()->email ?? Cookie::get('persistent_session_id') ?? 'Anonymous';
        \App\Models\Audit::audit('info', 'Cart', 'Item Added', $tire_id, "Prece pievienota: {$tire_id} ({$quantity}gab.) - {$identifier}");

        return response()->json([
            'success' => true,
            'cart' => $cart,
            'total_sum' => $totalSum,
            'quantity' => array_sum(array_column($cart['products'], 'quantity'))
        ]);
    }

    // Статический метод для добавления товара в корзину (для совместимости с другими контроллерами)
    public static function addProduct($model, $product_id, $quantity)
    {
        $cart = session()->get('cart', ['products' => [], 'total_sum' => 0]);

        // Если товар уже есть в корзине, обновляем количество
        if (isset($cart['products'][$product_id])) {
            $cart['products'][$product_id]['quantity'] += $quantity;
        } else {
            // Получаем информацию о товаре
            $product = $model::find($product_id);
            if ($product) {
                $cart['products'][$product_id] = [
                    'name' => $product->name ?? $product->title ?? 'Unknown Product',
                    'price' => $product->price ?? 0,
                    'quantity' => $quantity,
                    'image' => $product->image ?? null
                ];
            }
        }

        // Пересчитываем общую сумму
        $cart['total_sum'] = 0;
        foreach ($cart['products'] as $item) {
            $cart['total_sum'] += $item['price'] * $item['quantity'];
        }

        session()->put('cart', $cart);
        return $cart;
    }

    private function emailText($order) {
        $orderDetails = Utility::decode_info($order->order_details);
        $carDetails = Utility::decode_info($order->car_details);
        
        $out = '<div style="width: 70%; margin: 20px auto; font-family: Arial, Helvetica, sans-serif;">
            <div style="display: flex;">
                <div style="margin: 10px 25px;">
                    R1 Riepu serviss <br>
                    Kalnciema iela 39 <br>
                    Rīga, LV-1046 <br>
                    Tālrunis: +37167910555 <br>
                    E-pasts: <a href="mailto:info@r1riepas.lv">info@r1riepas.lv</a>
                </div>
                <div style="margin-left: auto;">
                    <img src="https://r1riepas.lv/img/r1-riepas-logo-1515661637.jpg" alt="" style="height: 100px;">
                </div>
            </div>
            <div style="background-color: lightgrey; padding: 5px;">
                <b>Pasūtījuma informācija</b>
            </div>
            <div style="margin: 10px 25px;">
                Pasūtījuma Nr. ' . $order->order_number . '<br>
                Pasūtījuma datums: ' . $order->created_at . '<br>
                Pasūtījuma stāvoklis: <b>Pasūtījums tiek pārbaudīts.</b> <br> <br>
                Menedžeris ar Jums sazināsies tuvākajā laikā, lai informētu par pasūtījuma gaitu.
            </div>
            <div style="background-color: lightgrey; padding: 5px;">
                <b>Pasūtītāja dati</b>
            </div>
            <div>
                <div style="margin: 10px 25px;">
                    E-pasts: ' . $order->email . '<br>
                    Pasūtītājs: ' . $order->customer_name . ' ' . $order->customer_surname . '<br>
                    Tālruņa numurs: ' . $order->phone_country_code . ' ' . $order->phone_number . '<br>';
                    if (isset($order->company_reg_nr) && !empty($order->company_reg_nr)) {
                        $out .= 'Reģistrācijas numurs: ' . $order->company_reg_nr . '<br>';
                        if (isset($order->company_pvn_nr) && !empty($order->company_pvn_nr)) $out .= 'PVN Numurs: ' . $order->company_pvn_nr . '<br>';
                        $out .= 'Uzņēmuma nosaukums: ' . $order->company_name . '<br>
                        Juridiskā adrese: ' . $order->company_address . '<br>';
                    }

                    if ($order->delivery_method == 1) {
                        if ($order->mounting_office == 1) {
                            $out .= 'Saņemšanas vieta: Ulbroka, Acones iela 2A';
                        } elseif ($order->mounting_office == 2) {
                            $out .= 'Saņemšanas vieta: Rīga, Kalnciema iela 39';
                        }
                    } else {
                        if ($order->delivery_city == 1) {
                            $out .= 'Piegādes adrese: Rīga, ' . $order->delivery_address;
                            if (isset($order->door_code) && !empty($order->door_code)) $out .= ', Durvju kods: ' . $order->door_code;
                        } elseif ($order->delivery_city == 2) {
                            $out .= 'Piegādes adrese: Salaspils, ' . $order->delivery_address;
                            if (isset($order->door_code) && !empty($order->door_code)) $out .= ', Durvju kods: ' . $order->door_code;
                        } else {
                            $out .= 'Piegādes adrese: Cits, ' . $order->delivery_address;
                            if (isset($order->door_code) && !empty($order->door_code)) $out .= ', Durvju kods: ' . $order->door_code;
                        }
                    }
                $out .= '</div>
                <div style="background-color: lightgrey; padding: 5px;">
                    <b>Pasūtītās preces</b>
                </div>
                <div style="margin: 10px 25px;">
                    <table style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                        <tr>
                            <th style="text-align: left;">Nosaukums</th>
                            <th style="text-align: center;">Skaits</th>
                            <th style="text-align: center;">Cena</th>
                            <th style="text-align: center;">Summa</th>
                        </tr>';
                        
                        $itemsSum = 0;
                        foreach ($orderDetails->products as $item) {
                            $itemTotal = $item->price * $item->quantity;
                            $itemsSum += $itemTotal;
                            $out .= '<tr>
                                <td>' . $item->name . '</td>
                                <td style="text-align: center;">' . $item->quantity . '</td>
                                <td style="text-align: center;">€ ' . $item->price . '</td>
                                <td style="text-align: center;">€ ' . $itemTotal . '</td>
                            </tr>';
                        }
                        
                        if ($order->mounting_price > 0) {
                            $out .= '<tr>
                                <td>Montāža</td>
                                <td style="text-align: center;">1</td>
                                <td style="text-align: center;">€ ' . $order->mounting_price . '</td>
                                <td style="text-align: center;">€ ' . $order->mounting_price . '</td>
                            </tr>';
                        }
                        
                        if ($order->delivery_price > 0) {
                            $out .= '<tr>
                                <td>Piegāde</td>
                                <td style="text-align: center;">1</td>
                                <td style="text-align: center;">€ ' . $order->delivery_price . '</td>
                                <td style="text-align: center;">€ ' . $order->delivery_price . '</td>
                            </tr>';
                        }
                        
                        if ($order->discount_value > 0) {
                            $discountAmount = $this->priceCalculationService->calculateDiscountAmount(
                                $itemsSum, 
                                $order->discount_type, 
                                $order->discount_value
                            );
                            
                            $out .= '<tr>
                                <td>Atlaižu kods (' . $order->promo_code . ')</td>
                                <td></td>
                                <td></td>
                                <td style="text-align: center;">€ -' . $discountAmount . '</td>
                            </tr>';
                        }
                        
                        $out .= '<tr>
                            <td></td>
                            <td style="text-align: center;"></td>
                            <td style="text-align: center;"><b>Kopā:</b></td>
                            <td style="text-align: center;">€ ' . $order->total_price . '</td>
                        </tr>
                    </table>
                </div>
            </div>
            <div style="background-color: lightgrey; padding: 5px;">
                <b>Pasūtītāja komentāri, piezīmes</b>
            </div>
            <div style="margin: 10px 25px;">';
                if (isset($order->comments) && !empty($order->comments)) $out .= $order->comments . '<br>';
                if ($carDetails && isset($carDetails->car_brand) && !empty($carDetails->car_brand)) $out .= 'Auto marka: ' . $carDetails->car_brand . '<br>';
                if ($carDetails && isset($carDetails->car_model) && !empty($carDetails->car_model)) $out .= 'Auto modelis: ' . $carDetails->car_model . '<br>';
                if ($carDetails && isset($carDetails->car_release_year) && !empty($carDetails->car_release_year)) $out .= 'Izlaiduma gads: ' . $carDetails->car_release_year . '<br>';
                if ($carDetails && isset($carDetails->car_engine_size) && !empty($carDetails->car_engine_size)) $out .= 'Dzinēja tilpums: ' . $carDetails->car_engine_size . '<br>';
            $out .= '</div>
            <div style="background-color: lightgrey; padding: 5px;">
                <b>Apmaksas informācija</b>
            </div>
            <div style="margin: 10px 25px;">';
                if ($order->payment_method == 1) {
                    $out .= 'Apmaksa saņemšanas brīdī';
                } elseif ($order->payment_method == 2) {
                    $out .= 'Bankas pārskaitījums';
                } else {
                    $out .= 'Tiešsaistes apmaksa';
                }
                $out .= '<br>
            </div>';
            if ($order->email_notification == 1) {
                $out .= '<div style="background-color:lightgrey;padding:5px"><b>Piekrītu, ka man tiks sūtīti paziņojumi par akcijām un jaunumiem uz norādīto e-pastu</b></div>';
            } else {
                $out .= '<div style="background-color:lightgrey;padding:5px"><b>Nepiekrītu, ka man tiks sūtīti paziņojumi par akcijām un jaunumiem uz norādīto e-pastu</b></div>';
            }
        $out .= '</div>';

        return $out;
    }
}
