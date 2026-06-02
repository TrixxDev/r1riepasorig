<?php

namespace App\Services;

use PDO;
use PDOException;
use RuntimeException;

class AccrualDatabaseService
{
    private ?PDO $pdo = null;

    /** @var array<string, mixed>|null */
    private ?array $connectionMeta = null;

    public function connection(): PDO
    {
        if ($this->pdo instanceof PDO) {
            return $this->pdo;
        }

        [$pdo, $meta] = $this->connectUsingConfiguredDriver();
        $this->pdo = $pdo;
        $this->connectionMeta = $meta;

        return $this->pdo;
    }

    /**
     * @return array<string, mixed>
     */
    public function connectionInfo(): array
    {
        if (!$this->connectionMeta) {
            $this->connection();
        }

        return $this->connectionMeta ?? [];
    }

    /**
     * @return array{0: PDO, 1: array<string, mixed>}
     */
    private function connectUsingConfiguredDriver(): array
    {
        $host = (string) config('accrual.host');
        $port = (string) config('accrual.port');
        $database = (string) config('accrual.database');
        $username = (string) config('accrual.username');
        $password = (string) config('accrual.password');
        $requestedDriver = strtolower((string) config('accrual.driver', 'auto'));

        if ($host === '' || $username === '') {
            throw new PDOException('Accrual database is not configured (ACCRUAL_IP / ACCRUAL_USER).');
        }

        $available = PDO::getAvailableDrivers();
        $driversToTry = $this->resolveDriversToTry($requestedDriver, $available);
        if ($driversToTry === []) {
            throw new PDOException(
                'No Accrual PDO driver available. Enable pdo_sqlsrv or pdo_odbc in php.ini. Available: '
                . implode(', ', $available)
            );
        }

        $errors = [];
        foreach ($driversToTry as $driver) {
            try {
                if ($driver === 'sqlsrv') {
                    return [
                        $this->connectSqlsrv($host, $port, $database, $username, $password),
                        [
                            'driver' => 'sqlsrv',
                            'dsn' => "sqlsrv:Server={$host},{$port};Database={$database}",
                            'host' => $host,
                            'port' => $port,
                            'database' => $database,
                        ],
                    ];
                }

                if ($driver === 'odbc') {
                    $odbcDriver = (string) config('accrual.odbc_driver', 'SQL Server');
                    $loginTimeout = (int) config('accrual.login_timeout', 10);
                    $dsn = "odbc:Driver={{$odbcDriver}};Server={$host},{$port};Database={$database};LoginTimeout={$loginTimeout}";

                    return [
                        $this->connectOdbc($dsn, $username, $password),
                        [
                            'driver' => 'odbc',
                            'dsn' => $dsn,
                            'host' => $host,
                            'port' => $port,
                            'database' => $database,
                            'odbc_driver' => $odbcDriver,
                        ],
                    ];
                }
            } catch (PDOException $e) {
                $errors[] = "{$driver}: {$e->getMessage()}";
            }
        }

        throw new PDOException("Accrual connection failed:\n" . implode("\n", $errors));
    }

    /**
     * @param  list<string>  $available
     * @return list<string>
     */
    private function resolveDriversToTry(string $requestedDriver, array $available): array
    {
        $preferred = $requestedDriver === 'odbc' ? ['odbc', 'sqlsrv'] : ['sqlsrv', 'odbc'];

        return array_values(array_filter(
            $preferred,
            static fn (string $driver): bool => in_array($driver, $available, true)
        ));
    }

    private function connectSqlsrv(
        string $host,
        string $port,
        string $database,
        string $username,
        string $password
    ): PDO {
        return new PDO(
            "sqlsrv:Server={$host},{$port};Database={$database}",
            $username,
            $password,
            $this->pdoOptions()
        );
    }

    private function connectOdbc(string $dsn, string $username, string $password): PDO
    {
        return new PDO($dsn, $username, $password, $this->pdoOptions());
    }

    /**
     * @return array<int, mixed>
     */
    private function pdoOptions(): array
    {
        return [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];
    }

    public function testQuery(): array
    {
        $pdo = $this->connection();
        $stmt = $pdo->query('SELECT TOP 1 PZId, PZNr, PZType, Datums FROM pzh WHERE Deleted = 0 ORDER BY PZId DESC');
        $row = $stmt->fetch();

        if (!$row) {
            throw new RuntimeException('Connected, but pzh table returned no rows.');
        }

        return $row;
    }
}
