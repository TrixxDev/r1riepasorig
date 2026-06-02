<?php

namespace App\Services;

use App\Helper\SmsSender;
use App\Http\Controllers\EmailController as Mailer;
use App\Models\Office;
use App\Models\Queue;
use App\Models\Service;
use App\Models\Slot;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Sends mail, SMS, WhatsApp (textmebot) and WebSocket notify after a public booking.
 * Intended to run via dispatch(...)->afterResponse() so the client gets JSON immediately.
 */
class FillSlotBookingNotifications
{
    public static function send(int $slotId, array $resultArray, int $officeId, string $time, bool $shouldSendWppToday): void
    {
        try {
            $slot = Slot::find($slotId);
            if (! $slot) {
                Log::warning('[FillSlotBookingNotifications] Slot not found', ['slotId' => $slotId]);

                return;
            }

            $result = json_decode(json_encode($resultArray, JSON_UNESCAPED_UNICODE), false);
            $queue = Queue::where('queue_id', $slot->queue_id)->first();
            if (! $queue) {
                Log::warning('[FillSlotBookingNotifications] Queue not found', ['queue_id' => $slot->queue_id]);

                return;
            }

            $smsText = $queue->parseNotification($queue->getOriginal()['notificationScheduleSMS'], $slot->date, $slot->iorder, $result, $time);

            if (! empty($result->email)) {
                $mailText = $queue->parseNotification($queue->getOriginal()['notificationEmail'], $slot->date, $slot->iorder, $result, $time);
                $mailer = new Mailer();
                $mailer->addRecipient($result->email);
                $bcc = 'karlis@r1riepas.lv';
                if ($bcc) {
                    $mailer->addBCC($bcc);
                }
                $mailer->subject = $queue->parseNotification($queue->getOriginal()['notificationSubject'], $slot->date, $slot->iorder, $result, $time);
                $mailer->message = $mailText;
                $mailer->send();
            }

            (new SmsSender)->sendSchedule((array) $result, $smsText, $slot, false);

            $urls = [];
            $wppParallelText = null;
            $wppParallelOfficeJid = null;

            $appTz = config('app.timezone', 'Europe/Riga');
            $slotDay = Carbon::parse($slot->date)->toDateString();
            $todayApp = Carbon::now($appTz)->toDateString();
            $slotDateIsToday = ($slotDay === $todayApp);
            // Do not send Urs/Krs messages for future-day bookings; guard even if the caller bool is wrong.
            $sendOfficeWpp = $shouldSendWppToday && $slotDateIsToday;

            if ($sendOfficeWpp) {
                $ursWpp = (string) config('services.whatsapp_parallel.wpp_group_urs', '120363130984594947@g.us');
                $krsWpp = (string) config('services.whatsapp_parallel.wpp_group_krs', '120363150684433547@g.us');
                $office = Office::where('office_id', $officeId)->first();
                $serviceModel = Service::where('service_id', $result->service)->first();
                if ($office && $serviceModel) {
                    $vehicle = str_replace(' ', '%20', $result->car_brand);
                    $userComment = (! empty($result->user_comment)) ? '%20|%20Piezīmes%20-%20' . str_replace([" ", "\n", "\r"], '%20', $result->user_comment) : '';
                    $model = str_replace(' ', '%20', $result->car_model);
                    $serviceTitle = str_replace(' ', '%20', $serviceModel->pdf_title);
                    $vehiclePlate = str_replace(' ', '%20', $result->lic_plate);
                    $discount = str_replace(' ', '%20', $slot->comment);
                    $discount = (! empty($slot->comment)) ? '%20|%20(' . $discount . ')' : '';

                    $rimsWith = isset($result->rimsWith) ? $result->rimsWith : null;
                    if (! empty($rimsWith)) {
                        if ($rimsWith == 1) {
                            $append = '%20-%20Riepas%20bez%20diskiem';
                            $appendPlain = ' - Riepas bez diskiem';
                        } else {
                            $append = '%20-%20Riepas%20ar%20diskiem';
                            $appendPlain = ' - Riepas ar diskiem';
                        }
                    } else {
                        $append = '';
                        $appendPlain = '';
                    }

                    $userCommentPlain = (! empty($result->user_comment))
                        ? ' | Piezīmes - '.preg_replace("/\s+/u", ' ', $result->user_comment)
                        : '';
                    $discountPlain = (! empty($slot->comment)) ? ' | ('.$slot->comment.')' : '';

                    $wppParallelText = 'Jauns pieraksts - '.$time.' | '.$result->car_brand.' '.$result->car_model
                        .' | '.$result->lic_plate.' | Pakalpojums - '.$serviceModel->pdf_title
                        .$appendPlain.$userCommentPlain.$discountPlain;

                    $wppParallelOfficeJid = ($office->office_id == 1) ? $ursWpp : $krsWpp;

                    if ($office->office_id == 1) {
                        $urls[] = 'http://api.textmebot.com/send.php?recipient=' . $ursWpp . '&apikey=d6nsRWNp1xpc&text=Jauns%20pieraksts%20-%20' . $time . '%20|%20' . $vehicle . '%20' . $model . '%20|%20' . $vehiclePlate . '%20|%20Pakalpojums%20-%20' . $serviceTitle . $append . $userComment . $discount;
                    } else {
                        $urls[] = 'http://api.textmebot.com/send.php?recipient=' . $krsWpp . '&apikey=d6nsRWNp1xpc&text=Jauns%20pieraksts%20-%20' . $time . '%20|%20' . $vehicle . '%20' . $model . '%20|%20' . $vehiclePlate . '%20|%20Pakalpojums%20-%20' . $serviceTitle . $append . $userComment . $discount;
                    }
                }
            }

            $requests = [];
            $mh = curl_multi_init();

            foreach ($urls as $k => $url) {
                $requests[$k] = [];
                $requests[$k]['url'] = $url;
                $requests[$k]['curl_handle'] = curl_init($url);
                curl_setopt($requests[$k]['curl_handle'], CURLOPT_RETURNTRANSFER, true);
                curl_multi_add_handle($mh, $requests[$k]['curl_handle']);
            }

            $stillRunning = false;
            do {
                curl_multi_exec($mh, $stillRunning);
            } while ($stillRunning);

            foreach ($requests as $k => $reqs) {
                curl_multi_remove_handle($mh, $reqs['curl_handle']);
                curl_close($requests[$k]['curl_handle']);
            }
            curl_multi_close($mh);

            $hadParallelPayload = ($wppParallelText !== null && $wppParallelOfficeJid !== null);
            $parallelTestEmpty = false;
            $parallelHttpOk = null;
            if (WhatsappParallelSender::isParallelApiEnabled() && $hadParallelPayload) {
                $wppParallelCfg = config('services.whatsapp_parallel', []);
                $parallelSender = new WhatsappParallelSender;
                $testMode = WhatsappParallelSender::isTestMode();
                $testTo = WhatsappParallelSender::testToStringFromConfig($wppParallelCfg['test_to'] ?? null);
                $to = $testMode ? $testTo : $wppParallelOfficeJid;
                if ($to === '') {
                    $parallelTestEmpty = $testMode;
                } else {
                    $parallelHttpOk = $parallelSender->send($to, $wppParallelText);
                }
            }

            WhatsappNotificationAudit::digestsForSlotAfterBooking([
                'urls' => $urls,
                'shouldSendWppToday' => $shouldSendWppToday,
                'slotDateIsToday' => $slotDateIsToday,
                'officeId' => $officeId,
                'serviceId' => $result->service ?? null,
                'slot' => $slot,
                'hadParallelPayload' => $hadParallelPayload,
                'parallelTestEmpty' => $parallelTestEmpty,
                'parallelHttpOk' => $parallelHttpOk,
            ]);

            (new AppointmentNotifyService)->notifyRecordCreated($slot->date, (int) $slot->queue_id, (int) $slot->iorder);
        } catch (\Throwable $e) {
            Log::error('[FillSlotBookingNotifications] ' . $e->getMessage(), ['exception' => $e]);
        }
    }
}
