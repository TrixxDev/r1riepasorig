<?php

namespace App\Http\Controllers;

use App\Services\AccrualPartnerService;
use Illuminate\Http\Request;
use PDOException;
use RuntimeException;

class AccrualPartnerController extends Controller
{
    public function search(Request $request, AccrualPartnerService $partners)
    {
        $query = trim((string) $request->query('q', ''));

        try {
            return response()->json([
                'items' => $partners->search($query, (int) $request->query('limit', 25)),
            ]);
        } catch (PDOException $e) {
            report($e);

            return response()->json([
                'error' => 'Neizdevās ielādēt klientu sarakstu: ' . $e->getMessage(),
            ], 503);
        }
    }

    public function store(Request $request, AccrualPartnerService $partners)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'regnr' => 'nullable|string|max:64',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:64',
            'email' => 'nullable|string|max:128',
        ]);

        try {
            $result = $partners->create($validated);

            return response()->json([
                'partner' => $result['partner'],
                'created' => $result['created'],
            ]);
        } catch (RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (PDOException $e) {
            report($e);

            return response()->json([
                'error' => 'Neizdevās izveidot klientu: ' . $e->getMessage(),
            ], 503);
        }
    }
}
