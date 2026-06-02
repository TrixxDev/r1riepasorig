<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SalaryController extends Controller
{
    private $pdo;

    public function __construct()
    {
        try {
            $this->pdo = new \PDO(
                "sqlsrv:Server=" . env('ACCRUAL_IP') . ",1444;Database=accrual",
                env('ACCRUAL_USER', 'sa'),
                env('ACCRUAL_PASS', 'cenzors'),
                [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC
                ]
            );
        } catch (\PDOException $e) {
            abort(500, "Database connection failed: " . $e->getMessage());
        }
    }

    public function mssqlTest()
    {
        try {
            $stmt = $this->pdo->query("SELECT 1 as test");
            $result = $stmt->fetch();
            return response()->json([
                'success' => true,
                'result' => $result
            ]);
        } catch (\PDOException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

public function findPerson(Request $request)
{
    $search = $request->get('search');

        try {
            if (is_numeric($search)) {
                $stmt = $this->pdo->prepare("
                    SELECT TOP 10 PersonId, Kods, Amats, Adres1, Talrunis, PersonProfId, Deleted
                    FROM undarbin
                    WHERE PersonId = :search
                    ORDER BY PersonId
                ");
                $stmt->execute(['search' => $search]);
            } else {
                $stmt = $this->pdo->prepare("
                    SELECT TOP 10 PersonId, Kods, Amats, Adres1, Talrunis, PersonProfId, Deleted
                    FROM undarbin
                    WHERE Amats LIKE :search OR Adres1 LIKE :search
                    ORDER BY PersonId
                ");
                $stmt->execute(['search' => "%$search%"]);
            }

            $persons = $stmt->fetchAll();

            return response()->json([
                'success' => true,
                'result' => $persons
            ]);
        } catch (\PDOException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function getSalaries(Request $request)
    {
        $personId = $request->get('person_id');
        $year = $request->get('year');
        $month = $request->get('month');

        try {
            $sql = "SELECT * FROM unalga WHERE PersonId = :personId";
            $params = ['personId' => $personId];

            if ($year && $month) {
                $start = $this->dateToAccrualFormat($year, $month, 1);
                $end   = $this->dateToAccrualFormat($year, $month, 31);
                $sql .= " AND Datums BETWEEN :start AND :end";
                $params['start'] = $start;
                $params['end']   = $end;
            } elseif ($year) {
                $start = $this->dateToAccrualFormat($year, 1, 1);
                $end   = $this->dateToAccrualFormat($year, 12, 31);
                $sql .= " AND Datums BETWEEN :start AND :end";
                $params['start'] = $start;
                $params['end']   = $end;
            }

            $sql .= " ORDER BY Datums DESC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);

            return response()->json([
                'success' => true,
                'result' => $stmt->fetchAll()
            ]);
        } catch (\PDOException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function getSickLeaves(Request $request)
    {
        $personId = $request->get('person_id');
        $year = $request->get('year');

        try {
            $sql = "SELECT * FROM pkslimiba WHERE PersonId = :personId";
            $params = ['personId' => $personId];

            if ($year) {
                $start = $this->dateToAccrualFormat($year, 1, 1);
                $end   = $this->dateToAccrualFormat($year, 12, 31);
                $sql .= " AND SakDat BETWEEN :start AND :end";
                $params['start'] = $start;
                $params['end']   = $end;
            }

            $sql .= " ORDER BY SakDat DESC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);

            return response()->json([
                'success' => true,
                'result' => $stmt->fetchAll()
            ]);
        } catch (\PDOException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    private function dateToAccrualFormat($year, $month, $day)
    {
        $date = new \DateTime("{$year}-{$month}-{$day}");
        $baseDate = new \DateTime('1899-12-30');
        $diff = $date->diff($baseDate);
        return $diff->days;
    }
}

