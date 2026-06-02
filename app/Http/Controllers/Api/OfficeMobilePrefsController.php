<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Per-office settings for the native mobile schedule app (lift grid: 8 vs 10 places).
 */
class OfficeMobilePrefsController extends Controller
{
    public function show(Request $request)
    {
        $request->validate([
            'office_id' => 'required|integer|in:1,2',
        ]);

        $officeId = (int) $request->query('office_id');
        $row = DB::table('office_mobile_prefs')->where('office_id', $officeId)->first();

        $count = $row && in_array((int) $row->lift_slot_count, [8, 10], true)
            ? (int) $row->lift_slot_count
            : 10;

        return response()->json(['lift_slot_count' => $count]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'office_id' => 'required|integer|in:1,2',
            'lift_slot_count' => 'required|integer|in:8,10',
        ]);

        DB::table('office_mobile_prefs')->updateOrInsert(
            ['office_id' => $validated['office_id']],
            [
                'lift_slot_count' => $validated['lift_slot_count'],
                'updated_at' => now(),
            ]
        );

        return response()->json([
            'lift_slot_count' => $validated['lift_slot_count'],
        ]);
    }
}
