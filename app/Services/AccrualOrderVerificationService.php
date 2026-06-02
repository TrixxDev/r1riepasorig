<?php

namespace App\Services;

use Carbon\Carbon;
use PDO;
use PDOException;
use PDOStatement;

class AccrualOrderVerificationService
{
    private const SELECT_HEADER_SQL = '
        SELECT TOP 1
            h.PZNr,
            h.Datums,
            h.Galasumma
        FROM pzh h WITH (NOLOCK)
        WHERE h.Deleted = 0
          AND h.WEB = :web
          AND h.PZType = :pzType
        ORDER BY h.PZId DESC
    ';

    private AccrualDatabaseService $db;

    public function __construct(AccrualDatabaseService $db)
    {
        $this->db = $db;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findByWebId(int $webId, int $pzType): ?array
    {
        if ($webId <= 0) {
            return null;
        }

        try {
            $pdo = $this->db->connection();
        } catch (PDOException $e) {
            report($e);

            return null;
        }

        return $this->fetchHeader($pdo, $webId, $pzType);
    }

    /**
     * Poll Accrual MSSQL until the document appears in pzh (by WEB id from XML).
     *
     * @return array{confirmed: bool, header: array<string, mixed>|null, error: string|null}
     */
    public function waitForCreation(int $webId, int $pzType, ?float $expectedTotal = null): array
    {
        if ($webId <= 0) {
            return ['confirmed' => false, 'header' => null, 'error' => 'invalid_web'];
        }

        $maxAttempts = max(1, (int) config('accrual.order_verify.max_attempts', 8));
        $intervalMs = max(50, (int) config('accrual.order_verify.interval_ms', 75));
        $maxWaitMs = max($intervalMs, (int) config('accrual.order_verify.max_wait_ms', 1500));
        $totalTolerance = (float) config('accrual.order_verify.total_tolerance', 0.02);

        try {
            $pdo = $this->db->connection();
            $stmt = $this->prepareHeaderStatement($pdo, $webId, $pzType);
        } catch (PDOException $e) {
            report($e);

            return [
                'confirmed' => false,
                'header' => null,
                'error' => 'db_connection',
            ];
        }

        $deadline = (int) (microtime(true) * 1000) + $maxWaitMs;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            $header = $this->fetchHeaderWithStatement($stmt);
            if ($header && $this->totalMatches($header, $expectedTotal, $totalTolerance)) {
                return [
                    'confirmed' => true,
                    'header' => $header,
                    'error' => null,
                ];
            }

            if ($attempt >= $maxAttempts) {
                break;
            }

            $nowMs = (int) (microtime(true) * 1000);
            if ($nowMs >= $deadline) {
                break;
            }

            $sleepMs = min($intervalMs, $deadline - $nowMs);
            if ($sleepMs > 0) {
                usleep($sleepMs * 1000);
            }
        }

        return [
            'confirmed' => false,
            'header' => null,
            'error' => 'timeout',
        ];
    }

    /**
     * @param  array<string, mixed>  $header
     */
    public function accrualDocumentLabel(array $header): string
    {
        $pznr = (int) ($header['PZNr'] ?? 0);
        if ($pznr <= 0) {
            return '';
        }

        $year = $this->accrualIntToDate((int) ($header['Datums'] ?? 0))->year;

        return $year . '-' . $pznr;
    }

    private function prepareHeaderStatement(PDO $pdo, int $webId, int $pzType): PDOStatement
    {
        $stmt = $pdo->prepare(self::SELECT_HEADER_SQL);
        $stmt->bindValue(':web', $webId, PDO::PARAM_INT);
        $stmt->bindValue(':pzType', $pzType, PDO::PARAM_INT);

        return $stmt;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function fetchHeader(PDO $pdo, int $webId, int $pzType): ?array
    {
        try {
            $stmt = $this->prepareHeaderStatement($pdo, $webId, $pzType);

            return $this->fetchHeaderWithStatement($stmt);
        } catch (PDOException $e) {
            report($e);

            return null;
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    private function fetchHeaderWithStatement(PDOStatement $stmt): ?array
    {
        try {
            $stmt->execute();
        } catch (PDOException $e) {
            report($e);

            return null;
        }

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    /**
     * @param  array<string, mixed>  $header
     */
    private function totalMatches(array $header, ?float $expectedTotal, float $tolerance): bool
    {
        if ($expectedTotal === null) {
            return true;
        }

        $actual = (float) ($header['Galasumma'] ?? 0);
        if ($actual <= 0) {
            return true;
        }

        return abs($actual - $expectedTotal) <= $tolerance;
    }

    private function accrualIntToDate(int $days): Carbon
    {
        return Carbon::create(1899, 12, 30)->addDays($days);
    }
}
