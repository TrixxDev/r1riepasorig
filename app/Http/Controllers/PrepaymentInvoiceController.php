<?php

namespace App\Http\Controllers;

use App\Services\PrepaymentInvoiceService;
use Illuminate\Http\Request;

class PrepaymentInvoiceController extends Controller
{
    public function showByOrderReference(Request $request, string $orderReference, PrepaymentInvoiceService $service)
    {
        $orderReference = strtoupper(trim($orderReference));
        $partnerName = trim((string) $request->query('partner', ''));
        $total = $request->query('total');
        $total = is_numeric($total) ? (float) $total : null;

        $data = $service->buildViewDataFromOrderReference(
            $orderReference,
            $partnerName !== '' ? $partnerName : null,
            $total
        );
        if ($data) {
            return view('invoices.prepayment', $this->withLayoutOffsets($data));
        }

        return view('invoices.prepayment_pending', [
            'orderReference' => $orderReference,
            'partnerName' => $partnerName,
            'total' => $total,
        ]);
    }

    public function showByYearAndPznr(int $year, int $pznr, PrepaymentInvoiceService $service)
    {
        $header = $service->findHeaderByYearAndPznr($year, $pznr);
        if (!$header) {
            abort(404, 'Rēķins priekšapmaksai nav atrasts.');
        }

        $data = $service->buildViewData($header);

        return view('invoices.prepayment', $this->withLayoutOffsets($data));
    }

    public function showByPznr(int $pznr, PrepaymentInvoiceService $service)
    {
        $data = $service->buildViewDataFromPznr($pznr);
        if (!$data) {
            abort(404, 'Rēķins priekšapmaksai nav atrasts.');
        }

        return view('invoices.prepayment', $this->withLayoutOffsets($data));
    }

    public function statusByOrderReference(Request $request, string $orderReference, PrepaymentInvoiceService $service)
    {
        $orderReference = strtoupper(trim($orderReference));
        $partnerName = trim((string) $request->query('partner', ''));
        $total = $request->query('total');
        $total = is_numeric($total) ? (float) $total : null;

        $header = $service->findHeaderByOrderReference($orderReference);
        if (!$header && $partnerName !== '' && $total !== null) {
            $header = $service->findHeaderByPartnerAndTotal($partnerName, $total);
        }

        $previewUrl = $header ? $service->prepaymentUrlFromHeader($header) : null;

        return response()->json([
            'ready' => (bool) $header,
            'pznr' => $header['PZNr'] ?? null,
            'previewUrl' => $previewUrl,
        ]);
    }

    private function withLayoutOffsets(array $data): array
    {
        $lineCount = max(1, count($data['lines']));
        $tableHeight = 12.48 * ($lineCount + 2);
        $totalsTop = 150.7 + $tableHeight + 3;

        $data['totalsTop'] = number_format($totalsTop, 1, '.', '');
        $data['wordsTop'] = number_format($totalsTop + 30.1, 1, '.', '');
        $data['notesTop'] = number_format($totalsTop + 50.1, 1, '.', '');

        return $data;
    }
}
