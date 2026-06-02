<?php

namespace App\Services;

use App\Models\Audit;
use App\Models\Slot;
use Throwable;

/**
 * User-visible audit trail (Audit model) for why WhatsApp messages were or were not sent.
 * Messages are in Latvian for admin review.
 */
final class WhatsappNotificationAudit
{
    public static function recordSlot(int $slotId, string $severity, string $message, ?Slot $slot = null): void
    {
        try {
            Audit::audit(
                $severity,
                'Notification',
                'WhatsApp — pieraksts',
                $slotId,
                $message,
                $slot
            );
        } catch (Throwable $e) {
            // Do not break booking flow if audit storage fails
        }
    }

    public static function recordOrder(int $orderId, string $severity, string $message, $order = null): void
    {
        try {
            Audit::audit(
                $severity,
                'Notification',
                'WhatsApp — veikals',
                $orderId,
                $message,
                $order
            );
        } catch (Throwable $e) {
        }
    }

    public static function digestsForSlotAfterBooking(array $state): void
    {
        $slot = $state['slot'];
        $slotId = (int) $slot->slot_id;
        $lines = [];
        $urls = $state['urls'];
        $shouldSendWppToday = (bool) $state['shouldSendWppToday'];
        $slotDateIsToday = array_key_exists('slotDateIsToday', $state)
            ? (bool) $state['slotDateIsToday']
            : true;
        $wppTimeWindowAndToday = $shouldSendWppToday && $slotDateIsToday;
        $officeId = (int) $state['officeId'];
        $serviceId = $state['serviceId'] ?? null;
        $hasParallel = WhatsappParallelSender::isParallelApiEnabled();
        $hadPayload = ! empty($state['hadParallelPayload']);
        $parallelTestEmpty = ! empty($state['parallelTestEmpty']);
        $parallelHttpOk = $state['parallelHttpOk'] ?? null;

        if (count($urls) > 0) {
            $lines[] = 'TextMeBot (ofisa grupa): pieprasījums izsūtīts uz api.textmebot.com (1 ziņa).';
        } elseif (! $slotDateIsToday) {
            $lines[] = 'TextMeBot: NAV sūtīts — ofisa grupai (Urs/Krs) tikai pierakstam, kura `slot.date` ir šodienas datums app timezone (Europe/Riga / config). Slot date: '.($slot->date ?? '?').'.';
        } elseif (! $shouldSendWppToday) {
            $lines[] = 'TextMeBot: NAV sūtīts — pēc laika loga: pašreizējam laikam jābūt starp `startSendWpp` un `endSendWpp` (skat. RecordController), ja pieraksts ir uz šodien.'
                .' Ja šodienas pierakstam paziņojums vēl jāaizsūtā, pārbaudiet laiku.';
        } else {
            $lines[] = 'TextMeBot: NAV sūtīts — laika logs un datums atbilst, bet nav URL. Iespējams, trūkst Office (office_id='. $officeId .') vai Service (service_id='. (string) $serviceId .') rindas datubāzē.';
        }

        if (! $hasParallel) {
            if ($hadPayload) {
                $lines[] = 'Paralēlais HTTP API: NAV ieslēgts. Lai ieslēgtu, .env: WHATSAPP_PARALLEL_ENABLED=true, WHATSAPP_PARALLEL_API_KEY=sk_... tad `php artisan config:clear` (vai atjauniniet config:cache).';
            } else {
                $lines[] = 'Paralēlais HTTP API: neko neattēlo, jo nav sūtāma satura — tas tiek veidots tikai tad, ja izpildās tie paši ofisa apstākļi (šodienas diena, WPP laika logs) un atrasti Office/Service kā TextMeBot.';
            }
        } elseif (! $hadPayload) {
            $lines[] = 'Paralēlais HTTP API: ieslēgts, bet nav nosūtāma teksta (nav izpildīti priekšnosacījumi, lai sastādītu tādu pašu ziņojumu kā gaidīto TextMeBot).';
        } elseif ($parallelTestEmpty) {
            $lines[] = 'Paralēlais HTTP API: ieslēgts, testa režīms, bet tukšs WHATSAPP_PARALLEL_TEST_TO — sūtīšana atcelta (paralelais kanāls).';
        } elseif ($parallelHttpOk === true) {
            $lines[] = 'Paralēlais HTTP API: atbilde OK (2xx), saņēmējs: testa numurs, ja WHATSAPP_PARALLEL_TEST_MODE, citādi ursWpp/krsWpp grupas JID.';
        } else {
            $lines[] = $parallelHttpOk === false
                ? 'Paralēlais HTTP API: kļūda (nav 2xx) vai izņēmums — tehnisks ieraksts: `'.WhatsappErrorLogger::logFilePath().'`.'
                : 'Paralēlais HTTP API: sūtīšana netika izpildīta (nekonfigurēts adrese u.c.) — skat. arī `'.WhatsappErrorLogger::logFilePath().'`.';
        }

        $message = implode("\n", $lines);
        $sev = 'info';
        if (count($urls) === 0 && $wppTimeWindowAndToday) {
            $sev = 'warning';
        }
        if ($hasParallel && $parallelTestEmpty) {
            $sev = 'warning';
        }
        if ($hasParallel && $hadPayload && $parallelHttpOk === false) {
            $sev = 'warning';
        }

        self::recordSlot($slotId, $sev, $message, $slot);
    }

    /**
     * @param  object  $order  orders_ row
     * @param  null|true|false  $parallelHttpResult  null if parallel not used or not sent
     */
    public static function digestsForOrder($order, int $orderId, string $orderNumber, ?bool $parallelHttpResult, bool $parallelTestToMissing): void
    {
        $lines = [
            'TextMeBot: izsūtīšanas mēģinājums uz pasūtījumu grupu (wpp_group_order / api.textmebot.com), pasūtījums Nr. '.$orderNumber.'. (HTTP atbildi šobrīd nenolasām — ja nav ziņas grupā, iespējama TextMeBot / atslēga problēma.)',
        ];

        if (! WhatsappParallelSender::isParallelApiEnabled()) {
            $lines[] = 'Paralēlais HTTP API: atslēgts (.env: WHATSAPP_PARALLEL_ENABLED, WHATSAPP_PARALLEL_API_KEY).';
        } elseif ($parallelTestToMissing) {
            $lines[] = 'Paralēlais HTTP API: ieslēgts, bet testa režīmā tukšs WHATSAPP_PARALLEL_TEST_TO — sūtīšana atcelta.';
        } elseif ($parallelHttpResult === true) {
            $lines[] = 'Paralēlais HTTP API: 2xx — kārtībā. Saņēmējs: testa numurs vai pasūtījumu grupa (wpp_group_order) atkarībā no Test mode.';
        } elseif ($parallelHttpResult === false) {
            $lines[] = 'Paralēlais HTTP API: kļūda — detaļas: `'.WhatsappErrorLogger::logFilePath().'`.';
        } else {
            $lines[] = 'Paralēlais HTTP: netika sūtīts (nosacījumi).';
        }

        $sev = 'info';
        if (WhatsappParallelSender::isParallelApiEnabled() && $parallelTestToMissing) {
            $sev = 'warning';
        }
        if (WhatsappParallelSender::isParallelApiEnabled() && $parallelHttpResult === false) {
            $sev = 'warning';
        }

        self::recordOrder($orderId, $sev, implode("\n", $lines), $order);
    }
}
