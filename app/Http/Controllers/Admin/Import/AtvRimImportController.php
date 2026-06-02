<?php

namespace App\Http\Controllers\Admin\Import;

use App\Http\Controllers\Controller;
use App\Models\Quadrim;
use App\Models\Quadrimbrand;
use App\Models\Quadrimmake;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Duell (or partner) tab-separated import for ATV / quadr rims.
 *
 * TODO: Replace column indices below when the real Duell rim feed sample is available.
 * Current layout is a deliberate placeholder (same delimiter style as admin/quadr import).
 */
class AtvRimImportController extends Controller
{
    /**
     * Expected tab-separated columns (0-based), adjust as needed:
     * 0 article, 1 brand, 2 model (make / tread), 3 d1, 4 d3, 5 skr, 6 pcd, 7 et,
     * 8 price1, 9 price2, 10 quantity (optional), 11 duell supplier code (optional, for future stock sync)
     */
    public function import(Request $request): RedirectResponse
    {
        $data = (string) $request->input('rows', '');
        $rows = explode("\n", trim($data));
        $out = '<p><strong>ATV disku imports</strong> — kolonnu kartējums ir jāpielāgo, kad būs Duell parauga fails.</p>';

        foreach ($rows as $idx => $row) {
            $row = trim($row);
            if ($row === '') {
                continue;
            }

            $fields = explode("\t", $row);
            if (count($fields) < 8) {
                $out .= '<p class="text-warning">Rinda ' . ($idx + 1) . ': pārāk maz lauku (gaidīti vismaz 8).</p>';
                continue;
            }

            $article = trim((string) ($fields[0] ?? ''));
            $brandName = trim((string) ($fields[1] ?? ''));
            $makeName = trim((string) ($fields[2] ?? ''));

            if ($article === '' || $brandName === '' || $makeName === '') {
                $out .= '<p class="text-warning">Rinda ' . ($idx + 1) . ': trūkst article / brand / model.</p>';
                continue;
            }

            $brand = Quadrimbrand::where('b_title', 'like', '%' . $brandName . '%')->first();
            if ($brand === null) {
                $brand = new Quadrimbrand();
                $brand->timestamps = false;
                $brand->b_title = $brandName;
                $brand->save();
                $out .= '<p>Jauns brends: ' . e($brandName) . '</p>';
            }

            $make = Quadrimmake::where('t_title', $makeName)->where('brand_id', $brand->brand_id)->first();
            if ($make === null) {
                $make = new Quadrimmake();
                $make->timestamps = false;
                $make->brand_id = $brand->brand_id;
                $make->t_title = $makeName;
                $make->save();
                $out .= '<p>Jauns modelis: ' . e($makeName) . '</p>';
            }

            $rim = Quadrim::where('article', $article)->first();
            $new = false;
            if ($rim === null) {
                $rim = new Quadrim();
                $rim->timestamps = false;
                $new = true;
            }

            $rim->make_id = $make->make_id;
            $rim->article = $article;
            $rim->d1 = $fields[3] ?? '';
            $rim->d3 = $fields[4] ?? '';
            $rim->skr = $fields[5] ?? '';
            $rim->pcd = $fields[6] ?? '';
            $rim->et = $fields[7] ?? '';
            $rim->price1 = $fields[8] ?? '';
            $rim->price2 = $fields[9] ?? '';
            $rim->quantity = isset($fields[10]) && $fields[10] !== '' ? $fields[10] : 0;

            if ($new) {
                $rim->visible_list = 1;
                $rim->visible_users = 1;
                $rim->urs_quantity = '';
                $rim->krs_quantity = '';
            }

            $rim->save();

            $out .= '<p>' . ($new ? 'Pievienots' : 'Atjaunināts') . ' disks: '
                . e(
                    $brandName . ' ' . $makeName . ' '
                    . (($fields[3] ?? '') . '*' . ($fields[4] ?? ''))
                    . ', PCD ' . ($fields[6] ?? '')
                )
                . ' — article <strong>' . e($article) . '</strong></p>';

            // Optional Duell supplier code — quadr_stock ties to tyres in this codebase; rim stock wiring TBD.
            $duellCode = trim((string) ($fields[11] ?? ''));
            if ($duellCode !== '') {
                $out .= '<!-- TODO: persist Duell supplier code "' . e($duellCode) . '" for rim_id ' . (int) $rim->rim_id . ' when stock table supports rims -->';
            }
        }

        return redirect()->back()->with('out', $out);
    }
}
