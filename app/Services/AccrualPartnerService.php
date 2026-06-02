<?php

namespace App\Services;

use PDO;
use PDOException;
use RuntimeException;

class AccrualPartnerService
{
    private AccrualDatabaseService $db;

    public function __construct(AccrualDatabaseService $db)
    {
        $this->db = $db;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function search(string $query, int $limit = 25): array
    {
        $query = trim($query);
        if ($query === '') {
            return [];
        }

        $limit = max(1, min($limit, 50));
        $needle = $this->sqlString('%' . $this->escapeLike($this->toDatabaseString($query)) . '%');

        $pdo = $this->db->connection();
        $stmt = $pdo->query("
            SELECT TOP {$limit}
                PartnerId,
                Nosaukums,
                RegNr,
                JurAdrese,
                Telefons,
                Epasts
            FROM unpartneri
            WHERE Deleted = 0
              AND ISNULL(Nosaukums, '') <> ''
              AND (
                Nosaukums LIKE {$needle} ESCAPE '\'
                OR RegNr LIKE {$needle} ESCAPE '\'
                OR JurAdrese LIKE {$needle} ESCAPE '\'
                OR Telefons LIKE {$needle} ESCAPE '\'
              )
            ORDER BY Nosaukums
        ");

        return array_map([$this, 'mapPartnerRow'], $stmt->fetchAll(PDO::FETCH_ASSOC) ?: []);
    }

    public function findById(int $partnerId): ?array
    {
        if ($partnerId <= 0) {
            return null;
        }

        $pdo = $this->db->connection();
        $stmt = $pdo->query("
            SELECT TOP 1
                PartnerId,
                Nosaukums,
                RegNr,
                JurAdrese,
                Telefons,
                Epasts
            FROM unpartneri
            WHERE Deleted = 0
              AND PartnerId = {$partnerId}
        ");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->mapPartnerRow($row) : null;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{partner: array<string, mixed>, created: bool}
     */
    public function create(array $data): array
    {
        $name = trim((string) ($data['name'] ?? ''));
        if ($name === '') {
            throw new RuntimeException('Klienta nosaukums ir obligāts.');
        }

        $regNr = trim((string) ($data['regnr'] ?? ''));
        $address = trim((string) ($data['address'] ?? ''));
        $phone = trim((string) ($data['phone'] ?? ''));
        $email = trim((string) ($data['email'] ?? ''));

        $existing = $this->findDuplicate($name, $regNr);
        if ($existing) {
            return ['partner' => $existing, 'created' => false];
        }

        $pdo = $this->db->connection();
        $nextId = (int) $pdo->query('SELECT ISNULL(MAX(PartnerId), 0) + 1 AS NextId FROM unpartneri')->fetchColumn();

        $pdo->exec("
            INSERT INTO unpartneri (
                PartnerId,
                Veids,
                Nosaukums,
                RegNr,
                JurAdrese,
                Telefons,
                Epasts,
                Deleted
            ) VALUES (
                {$nextId},
                {$this->detectVeids($name, $regNr)},
                {$this->sqlString($this->toDatabaseString($name))},
                {$this->sqlNullableString($regNr)},
                {$this->sqlNullableString($address)},
                {$this->sqlNullableString($phone)},
                {$this->sqlNullableString($email)},
                0
            )
        ");

        $partner = $this->findById($nextId);
        if (!$partner) {
            throw new RuntimeException('Klients tika izveidots, bet nav atrasts pēc saglabāšanas.');
        }

        return ['partner' => $partner, 'created' => true];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function findDuplicate(string $name, string $regNr): ?array
    {
        $pdo = $this->db->connection();

        if ($regNr !== '') {
            $regNrSql = $this->sqlString($this->toDatabaseString($regNr));
            $stmt = $pdo->query("
                SELECT TOP 1
                    PartnerId,
                    Nosaukums,
                    RegNr,
                    JurAdrese,
                    Telefons,
                    Epasts
                FROM unpartneri
                WHERE Deleted = 0
                  AND RegNr = {$regNrSql}
            ");
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                return $this->mapPartnerRow($row);
            }
        }

        $nameSql = $this->sqlString($this->toDatabaseString($name));
        $stmt = $pdo->query("
            SELECT TOP 1
                PartnerId,
                Nosaukums,
                RegNr,
                JurAdrese,
                Telefons,
                Epasts
            FROM unpartneri
            WHERE Deleted = 0
              AND LTRIM(RTRIM(Nosaukums)) = {$nameSql}
        ");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->mapPartnerRow($row) : null;
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function mapPartnerRow(array $row): array
    {
        $name = trim($this->toUtf8($row['Nosaukums'] ?? ''));
        $regNr = trim($this->toUtf8($row['RegNr'] ?? ''));
        $address = trim($this->toUtf8($row['JurAdrese'] ?? ''));

        $label = $name;
        if ($regNr !== '') {
            $label .= ', ' . $regNr;
        }

        return [
            'id' => (int) $row['PartnerId'],
            'name' => $name,
            'regnr' => $regNr,
            'address' => $address,
            'phone' => trim($this->toUtf8($row['Telefons'] ?? '')),
            'email' => trim($this->toUtf8($row['Epasts'] ?? '')),
            'label' => $label,
        ];
    }

    private function toUtf8($value): string
    {
        $value = (string) $value;
        if ($value === '' || mb_check_encoding($value, 'UTF-8')) {
            return $value;
        }

        $converted = @iconv('Windows-1252', 'UTF-8//IGNORE', $value);

        return $converted !== false ? $converted : mb_convert_encoding($value, 'UTF-8', 'Windows-1252');
    }

    private function toDatabaseString(string $value): string
    {
        if ($value === '' || !mb_check_encoding($value, 'UTF-8')) {
            return $value;
        }

        $converted = @iconv('UTF-8', 'Windows-1252//TRANSLIT', $value);

        return $converted !== false ? $converted : $value;
    }

    private function escapeLike(string $value): string
    {
        return str_replace(
            ['\\', '%', '_', '['],
            ['\\\\', '\\%', '\\_', '[[]'],
            $value
        );
    }

    private function sqlString(string $value): string
    {
        return "'" . str_replace("'", "''", $value) . "'";
    }

    private function sqlNullableString(string $value): string
    {
        $value = trim($value);

        return $value !== '' ? $this->sqlString($this->toDatabaseString($value)) : 'NULL';
    }

    private function detectVeids(string $name, string $regNr): int
    {
        if (preg_match('/\b(SIA|IK|KS|AS|B\.?I\.?)\b/ui', $name)) {
            return 1;
        }

        if ($regNr !== '' && preg_match('/^LV?\d{11}$/i', $regNr)) {
            return 1;
        }

        return 1;
    }
}
