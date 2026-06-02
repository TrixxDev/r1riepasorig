<?php

namespace App\Services;

use Carbon\Carbon;
use PDOException;

class PrepaymentInvoiceService
{
    private AccrualDatabaseService $db;

    public function __construct(AccrualDatabaseService $db)
    {
        $this->db = $db;
    }

    public function findHeaderByOrderReference(string $orderReference): ?array
    {
        return $this->fetchHeader(
            '('
            . 'h.Piezimes = :ref'
            . ' OR h.Piezimes LIKE :refAfterSpace'
            . ' OR h.Piezimes LIKE :refBeforeSpace'
            . ' OR h.Piezimes LIKE :refWrappedSpace'
            . ' OR h.Piezimes LIKE :refAfterComma'
            . ' OR h.Piezimes LIKE :refBeforeComma'
            . ' OR h.Piezimes LIKE :refEnd'
            . ')',
            [
                'ref' => $orderReference,
                'refAfterSpace' => $orderReference . ' %',
                'refBeforeSpace' => '% ' . $orderReference,
                'refWrappedSpace' => '% ' . $orderReference . ' %',
                'refAfterComma' => $orderReference . ',%',
                'refBeforeComma' => '%,' . $orderReference . '%',
                'refEnd' => '%' . $orderReference,
            ]
        );
    }

    public function findHeaderByPznr(int $pznr): ?array
    {
        return $this->fetchHeader('h.PZNr = :pznr', ['pznr' => $pznr]);
    }

    public function findHeaderByYearAndPznr(int $year, int $pznr): ?array
    {
        $startDate = Carbon::create($year, 1, 1)->startOfDay();
        $endDate = Carbon::create($year, 12, 31)->endOfDay();

        $fromDate = $this->carbonToAccrualInt($startDate);
        $toDate = $this->carbonToAccrualInt($endDate);

        return $this->fetchHeader(
            'h.PZNr = :pznr AND h.Datums BETWEEN :fromDate AND :toDate',
            [
                'pznr' => $pznr,
                'fromDate' => $fromDate,
                'toDate' => $toDate,
            ]
        );
    }

    public function findHeaderByPartnerAndTotal(string $partnerName, float $total): ?array
    {
        $fromDate = $this->carbonToAccrualInt(now()->subDay()->startOfDay());
        $toDate = $this->carbonToAccrualInt(now()->addDay()->startOfDay());
        $totalTolerance = 0.02;

        return $this->fetchHeader(
            'p.Nosaukums LIKE :partnerName AND h.Datums BETWEEN :fromDate AND :toDate AND ABS(h.Galasumma - :total) <= :totalTolerance',
            [
                'partnerName' => '%' . $partnerName . '%',
                'fromDate' => $fromDate,
                'toDate' => $toDate,
                'total' => $total,
                'totalTolerance' => $totalTolerance,
            ]
        );
    }

    public function buildViewDataFromOrderReference(string $orderReference, ?string $partnerName = null, ?float $total = null): ?array
    {
        $header = $this->findHeaderByOrderReference($orderReference);
        if (!$header && $partnerName !== null && $partnerName !== '' && $total !== null) {
            $header = $this->findHeaderByPartnerAndTotal($partnerName, $total);
        }
        if (!$header) {
            return null;
        }

        return $this->buildViewData($header, $orderReference);
    }

    public function buildViewDataFromPznr(int $pznr): ?array
    {
        $header = $this->findHeaderByPznr($pznr);
        if (!$header) {
            return null;
        }

        return $this->buildViewData($header);
    }

    public function prepaymentUrlFromHeader(array $header): ?string
    {
        if (!isset($header['PZNr'], $header['Datums'])) {
            return null;
        }

        $year = $this->accrualIntToDate((int) $header['Datums'])->year;
        $pznr = (int) $header['PZNr'];

        return url('/prepayment-invoice/' . $year . '-' . $pznr);
    }

    private function fetchHeader(string $condition, array $params): ?array
    {
        try {
            $pdo = $this->db->connection();
        } catch (PDOException $e) {
            report($e);
            return null;
        }

        $pzType = (int) config('accrual.prepayment_pz_type', 8);

        $sql = "
            SELECT TOP 1
                h.PZId,
                h.PZNr,
                h.Datums,
                h.Summa,
                h.PVNSumma,
                h.Galasumma,
                h.PVN,
                h.Piezimes,
                h.ApmVeidsId,
                p.Nosaukums AS PartnerName,
                p.RegNr AS PartnerRegNr,
                p.JurAdrese AS PartnerAddress,
                a.Nosaukums AS PaymentTerms
            FROM pzh h
            LEFT JOIN unpartneri p ON p.PartnerId = h.PartnerId
            LEFT JOIN unapmveidi a ON a.ApmVeidsId = h.ApmVeidsId AND a.Deleted = 0
            WHERE h.Deleted = 0
              AND h.PZType = :pzType
              AND {$condition}
            ORDER BY h.PZId DESC
        ";

        try {
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':pzType', $pzType, \PDO::PARAM_INT);
            foreach ($params as $key => $value) {
                if ($key === 'fromDate' || $key === 'toDate' || $key === 'pznr') {
                    $stmt->bindValue(':' . $key, $value, \PDO::PARAM_INT);
                } elseif ($key === 'total' || $key === 'totalTolerance') {
                    $stmt->bindValue(':' . $key, $value);
                } else {
                    $stmt->bindValue(':' . $key, $value);
                }
            }
            $stmt->execute();
        } catch (PDOException $e) {
            report($e);

            return null;
        }

        $row = $stmt->fetch();

        return $row ? $this->normalizeAccrualRow($row) : null;
    }

    public function buildViewData(array $header, ?string $orderReference = null): array
    {
        $lines = $this->fetchLines((int) $header['PZId'], (int) ($header['PVN'] ?? 2100));
        $sumNet = (float) ($header['Summa'] ?? 0);
        $sumVat = (float) ($header['PVNSumma'] ?? 0);
        $sumTotal = round((float) ($header['Galasumma'] ?? ($sumNet + $sumVat)), 2);

        if ($sumNet <= 0 && count($lines) > 0) {
            $sumNet = array_sum(array_map(static fn ($line) => (float) $line['sum'], $lines));
        }

        $totalQty = array_sum(array_map(static fn ($line) => (float) $line['qty_raw'], $lines));
        $invoiceDate = $this->accrualIntToDate((int) $header['Datums']);

        return [
            'pznr' => (int) $header['PZNr'],
            'dateText' => $this->formatLatvianDate($invoiceDate),
            'supplier' => config('accrual.supplier'),
            'payerLine1' => $this->formatPayerLine1($header),
            'payerLine2' => $this->headerString($header, 'PartnerAddress'),
            'paymentTerms' => $this->headerString($header, 'PaymentTerms') ?: 'Pārskaitījums',
            'lines' => $lines,
            'totalQty' => $this->formatNumber($totalQty, 3),
            'totalSum' => $this->formatNumber($sumNet, 2),
            'vatRate' => '21.0',
            'vatSum' => $this->formatNumber($sumVat, 2),
            'grandTotal' => $this->formatNumber($sumTotal, 2),
            'amountWords' => $this->amountToWords($sumTotal),
            'notes' => $this->formatNotes($this->headerString($header, 'Piezimes'), $orderReference),
        ];
    }

    private function fetchLines(int $pzId, int $pvnBasisPoints): array
    {
        try {
            $pdo = $this->db->connection();
        } catch (PDOException $e) {
            return [];
        }

        $stmt = $pdo->prepare("
            SELECT Nosaukums, Mervieniba, Daudzums, Cena, Likme
            FROM pzd
            WHERE PZId = :pzId AND Deleted = 0
            ORDER BY PZArticleId
        ");
        $stmt->execute(['pzId' => $pzId]);
        $rows = array_map([$this, 'normalizeAccrualRow'], $stmt->fetchAll());

        $lines = [];
        $vatRate = ($pvnBasisPoints > 0 ? $pvnBasisPoints : 2100) / 10000;

        foreach ($rows as $index => $row) {
            $qty = (float) $row['Daudzums'];
            $price = (float) $row['Cena'];
            $lineLikme = isset($row['Likme']) ? ((float) $row['Likme']) / 10000 : $vatRate;
            $priceWithVat = round($price * (1 + $lineLikme), 2);

            $lines[] = [
                'nr' => ($index + 1) . '.',
                'name' => $this->rowString($row, 'Nosaukums'),
                'unit' => trim($this->rowString($row, 'Mervieniba')) ?: 'gab.',
                'qty' => $this->formatNumber($qty, 3),
                'qty_raw' => $qty,
                'price' => $this->formatNumber($price, 5),
                'priceWithVat' => $this->formatNumber($priceWithVat, 2),
                'sum' => $this->formatNumber($qty * $price, 2),
            ];
        }

        return $lines;
    }

    private function formatPayerLine1(array $header): string
    {
        $name = $this->headerString($header, 'PartnerName');
        $regNr = $this->headerString($header, 'PartnerRegNr');

        if ($name === '') {
            return 'Klients';
        }

        if ($regNr !== '') {
            return $name . ', ' . $regNr;
        }

        return $name;
    }

    private function formatNotes(string $piezimes, ?string $orderReference): string
    {
        $notes = trim($piezimes);
        if ($notes === '') {
            return '';
        }

        if ($orderReference) {
            $notes = trim(str_replace($orderReference, '', $notes));
        }

        $notes = trim(preg_replace('/\s+/', ' ', $notes) ?? $notes);
        if ($notes === '') {
            return '';
        }

        if (preg_match('/(\d{5,})$/', $notes, $matches)) {
            return $matches[1];
        }

        return $notes;
    }

    private function accrualIntToDate(int $days): Carbon
    {
        return Carbon::create(1899, 12, 30)->addDays($days);
    }

    private function carbonToAccrualInt(Carbon $date): int
    {
        return Carbon::create(1899, 12, 30)->startOfDay()->diffInDays($date->copy()->startOfDay());
    }

    private function formatLatvianDate(Carbon $date): string
    {
        $days = [
            'svētdiena', 'pirmdiena', 'otrdiena', 'trešdiena',
            'ceturtdiena', 'piektdiena', 'sestdiena',
        ];
        $months = [
            1 => 'janvāris', 2 => 'februāris', 3 => 'marts', 4 => 'aprīlis',
            5 => 'maijs', 6 => 'jūnijs', 7 => 'jūlijs', 8 => 'augusts',
            9 => 'septembris', 10 => 'oktobris', 11 => 'novembris', 12 => 'decembris',
        ];

        return sprintf(
            '%s, %d. gada %d. %s',
            $days[$date->dayOfWeek],
            $date->year,
            $date->day,
            $months[$date->month]
        );
    }

    private function formatNumber(float $value, int $decimals): string
    {
        return number_format($value, $decimals, ',', '');
    }

    private function amountToWords(float $amount): string
    {
        $euros = (int) floor($amount + 0.00001);
        $cents = (int) round(($amount - $euros) * 100);

        $words = $this->numberToWordsLv($euros);
        $words = $words === 'nulle' ? 'Nulle' : mb_convert_case($words, MB_CASE_TITLE, 'UTF-8');

        return $words . ' eiro un ' . $cents . ' centi';
    }

    private function numberToWordsLv(int $number): string
    {
        if ($number === 0) {
            return 'nulle';
        }

        $ones = ['', 'viens', 'divi', 'trīs', 'četri', 'pieci', 'seši', 'septiņi', 'astoņi', 'deviņi'];
        $teens = ['desmit', 'vienpadsmit', 'divpadsmit', 'trīspadsmit', 'četrpadsmit', 'piecpadsmit', 'sešpadsmit', 'septiņpadsmit', 'astoņpadsmit', 'deviņpadsmit'];
        $tens = ['', '', 'divdesmit', 'trīsdesmit', 'četrdesmit', 'piecdesmit', 'sešdesmit', 'septiņdesmit', 'astoņdesmit', 'deviņdesmit'];
        $hundreds = ['', 'simts', 'divi simti', 'trīs simti', 'četri simti', 'pieci simti', 'seši simti', 'septiņi simti', 'astoņi simti', 'deviņi simti'];

        $underThousand = null;
        $underThousand = function (int $n) use (&$underThousand, $ones, $teens, $tens, $hundreds): string {
            if ($n === 0) {
                return '';
            }
            if ($n < 10) {
                return $ones[$n];
            }
            if ($n < 20) {
                return $teens[$n - 10];
            }
            if ($n < 100) {
                $ten = intdiv($n, 10);
                $one = $n % 10;

                return trim($tens[$ten] . ($one ? ' ' . $ones[$one] : ''));
            }

            $hundred = intdiv($n, 100);
            $rest = $n % 100;

            return trim($hundreds[$hundred] . ($rest ? ' ' . $underThousand($rest) : ''));
        };

        $parts = [];
        $millions = intdiv($number, 1000000);
        $number %= 1000000;
        $thousands = intdiv($number, 1000);
        $rest = $number % 1000;

        if ($millions > 0) {
            $parts[] = $underThousand($millions) . ($millions === 1 ? ' miljons' : ' miljoni');
        }
        if ($thousands > 0) {
            $parts[] = $underThousand($thousands) . ($thousands === 1 ? ' tūkstotis' : ' tūkstoši');
        }
        if ($rest > 0) {
            $parts[] = $underThousand($rest);
        }

        return trim(implode(' ', $parts));
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function normalizeAccrualRow(array $row): array
    {
        foreach ($row as $key => $value) {
            if (is_string($value)) {
                $row[$key] = $this->toUtf8($value);
            }
        }

        return $row;
    }

    /**
     * @param  array<string, mixed>  $header
     */
    private function headerString(array $header, string $field): string
    {
        return trim($this->rowString($header, $field));
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function rowString(array $row, string $field): string
    {
        if (array_key_exists($field, $row)) {
            return (string) $row[$field];
        }

        $lower = strtolower($field);
        foreach ($row as $key => $value) {
            if (is_string($key) && strtolower($key) === $lower) {
                return (string) $value;
            }
        }

        return '';
    }

    private function toUtf8($value): string
    {
        $value = (string) $value;
        if ($value === '' || mb_check_encoding($value, 'UTF-8')) {
            return $value;
        }

        $converted = @iconv('Windows-1252', 'UTF-8//IGNORE', $value);
        if ($converted !== false) {
            return $converted;
        }

        return mb_convert_encoding($value, 'UTF-8', 'Windows-1252');
    }
}
