<?php

namespace App\Services;

use App\Helper\Utility;
use App\Http\Controllers\EmailController as Mailer;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class EmailService
{
    protected $priceCalculationService;

    public function __construct(PriceCalculationService $priceCalculationService)
    {
        $this->priceCalculationService = $priceCalculationService;
    }

    /**
     * Отправить письмо о заказе (клиент + BCC менеджерам). Повторно не шлёт в течение 30 дней.
     */
    public function sendOrderConfirmation(object $order): bool
    {
        $orderId = $order->id ?? null;
        if (!$orderId || empty($order->email)) {
            return false;
        }

        $cacheKey = 'order_confirmation_email_sent:' . $orderId;
        if (Cache::get($cacheKey)) {
            return true;
        }

        try {
            $mailer = new Mailer();
            $mailer->addRecipient($order->email);
            $mailer->addBCC('karlis@r1riepas.lv');
            $mailer->addBCC('info@r1.com.lv');
            $mailer->subject = env('MAIL_CART_SUBJECT', 'R1riepas.lv internetveikala pasūtījums');
            $mailer->message = $this->generateOrderEmail($order);

            if ($mailer->send()) {
                Cache::put($cacheKey, 1, now()->addDays(30));
                return true;
            }
        } catch (\Exception $e) {
            Log::error('Order confirmation email failed: ' . $e->getMessage(), [
                'order_id' => $orderId,
            ]);
        }

        return false;
    }

    /**
     * Сформировать HTML для email-уведомления о заказе
     *
     * @param object $details Данные заказа
     * @return string HTML содержимое письма
     */
    public function generateOrderEmail($details)
    {
        $orderDisplayNumber = isset($details->order_number) && !is_null($details->order_number)
            ? $details->order_number
            : $details->id;

        $orderDecoded = Utility::decode_info($details->order_details);

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
                Pasūtījuma Nr. ' . $orderDisplayNumber . '<br>
                Pasūtījuma datums: ' . $details->created_at . '<br>
                Pasūtījuma stāvoklis: <b>Pasūtījums tiek pārbaudīts.</b> <br> <br>
                Menedžeris ar Jums sazināsies tuvākajā laikā, lai informētu par pasūtījuma gaitu.
            </div>
            <div style="background-color: lightgrey; padding: 5px;">
                <b>Pasūtītāja dati</b>
            </div>
            <div>
                <div style="margin: 10px 25px;">
                    E-pasts: ' . $details->email . '<br>
                    Pasūtītājs: ' . $details->customer_name . ' ' . $details->customer_surname . '<br>
                    Tālruņa numurs: ' . $details->phone_number . '<br>';
                    if (!is_null($details->company_reg_nr)) {
                        $out .= 'Reģistrācijas numurs: ' . $details->company_reg_nr . '<br>';
                        if (!is_null($details->company_pvn_number)) $out .= 'PVN Numurs: ' . $details->company_pvn_number . '<br>';
                        $out .= 'Uzņēmuma nosaukums: ' . $details->company_name . '<br>
                                Juridiskā adrese: ' . $details->company_address . '<br>';
                    }

                    if (!is_null($details->mounting_office) && $details->mounting_office == 1) {
                        $out .= 'Saņemšanas vieta: Ulbroka, Acones iela 2A';
                    } elseif (!is_null($details->mounting_office) && $details->mounting_office == 2) {
                        $out .= 'Saņemšanas vieta: Rīga, Kalnciema iela 39';
                    } else {
                        if (!is_null($details->delivery_city) && $details->delivery_city == 1) {
                            $out .= 'Piegādes adrese: Rīga, ' . $details->delivery_address;
                            if (!is_null($details->door_code)) $out .= ', Durvju kods: ' . $details->door_code;
                        } elseif (!is_null($details->delivery_city) && $details->delivery_city == 3) {
                            $out .= 'Piegādes adrese: Cits, ' . $details->delivery_address;
                            if (!is_null($details->door_code)) $out .= ', Durvju kods: ' . $details->door_code;
                        } else {
                            $out .= 'Piegādes adrese: ' . $details->delivery_address;
                            if (!is_null($details->door_code)) $out .= ', Durvju kods: ' . $details->door_code;
                        }
                    }
            $out .= '</div>
                <div style="background-color: lightgrey; padding: 5px;">
                    <b>Pasūtītās preces</b>
                </div>
                <div style="margin: 10px 25px;">
                    <table style="border-collapse: collapse;
                                  border-spacing: 0;
                                  width: 100%;">
                        <tr>
                            <th style="text-align: left;">Nosaukums</th>
                            <th style="text-align: center;">Skaits</th>
                            <th style="text-align: center;">Cena</th>
                            <th style="text-align: center;">Summa</th>
                        </tr>';
                        $itemsSum = 0;
                        foreach ($orderDecoded->products as $item) {
                            $itemTotal = $item->price * $item->quantity;
                            $itemsSum += $itemTotal;
                            $out .= '<tr>
                                    <td>' . $item->name . '</td>
                                    <td style="text-align: center;">' . $item->quantity . '</td>
                                    <td style="text-align: center;">€ ' . $item->price . '</td>
                                    <td style="text-align: center;">€ ' . $itemTotal . '</td>
                                  </tr>';
                        }
                        if (!is_null($details->mounting_price) && $details->mounting_price > 0) {
                            $out .= '<tr>
                                    <td>Montāža</td>
                                    <td style="text-align: center;">1</td>
                                    <td style="text-align: center;">€ ' . $details->mounting_price . '</td>
                                    <td style="text-align: center;">€ ' . $details->mounting_price . '</td>
                                  </tr>';
                        }
                        if (!is_null($details->delivery_price) && $details->delivery_price > 0) {
                            $out .= '<tr>
                                    <td>Piegāde</td>
                                    <td style="text-align: center;">1</td>
                                    <td style="text-align: center;">€ ' . $details->delivery_price . '</td>
                                    <td style="text-align: center;">€ ' . $details->delivery_price . '</td>
                                  </tr>';
                        }

                        $discountAmount = $this->priceCalculationService->calculateDiscountAmount(
                            $itemsSum,
                            $details->discount_type, 
                            $details->discount_value
                        );

                        $finalPrice = $itemsSum - $discountAmount;

                        if (!is_null($details->mounting_price) && $details->mounting_price > 0) {
                            $finalPrice += $details->mounting_price;
                        }

                        if (!is_null($details->delivery_price) && $details->delivery_price > 0) {
                            $finalPrice += $details->delivery_price;
                        }

            $out .= '<tr>
                        <td></td>
                        <td style="text-align: center;"></td>
                        <td style="text-align: center;"><b>Kopā:</b></td>';
            $out .= '<td style="text-align: center;">€ ' . $finalPrice . '</td>';
            $out .= '</tr>
                </table>
            </div>
        </div>';
        if (!is_null($details->comments) || !is_null($details->car_details)) {
            $out .= '<div style="background-color: lightgrey; padding: 5px;">
            <b>Pasūtītāja komentāri, piezīmes</b>
          </div>
          <div style="margin: 10px 25px;">';
            if (!is_null($details->comments)) $out .= $details->comments . '<br>';

            if (!is_null($details->car_details)) {
                $car_details = Utility::decode_info($details->car_details);
                if (!is_null($car_details->car_brand)) $out .= 'Auto marka: ' . $car_details->car_brand . '<br>';
                if (!is_null($car_details->car_model)) $out .= 'Auto modelis: ' . $car_details->car_model . '<br>';
                if (!is_null($car_details->car_release_year)) $out .= 'Izlaiduma gads: ' . $car_details->car_release_year . '<br>';
                if (!is_null($car_details->car_engine_size)) $out .= 'Dzinēja tilpums: ' . $car_details->car_engine_size . '<br>';
            }
            $out .= '</div>';
        }
    $out .= '<div style="background-color: lightgrey; padding: 5px;">
        <b>Apmaksas informācija</b>
      </div>
      <div style="margin: 10px 25px;">';
    if ($details->payment_method == 1) {
        $out .= 'Apmaksa saņemšanas brīdī';
    } elseif ($details->payment_method == 2) {
        $out .= 'Bankas pārskaitījums';
    } else {
        $out .= 'Tiešsaistes apmaksa';
    }
    $out .= '<br>
      </div>';
    if ($details->email_notification == 1) {
        $out .= '<div style="background-color:lightgrey;padding:5px"><b>Piekrītu, ka man tiks sūtīti paziņojumi par akcijām un jaunumiem uz norādīto e-pastu</b></div>';
    } else {
        $out .= '<div style="background-color:lightgrey;padding:5px"><b>Nepiekrītu, ka man tiks sūtīti paziņojumi par akcijām un jaunumiem uz norādīto e-pastu</b></div>';
    }
    $out .= '</div>';

    return $out;
    }
} 