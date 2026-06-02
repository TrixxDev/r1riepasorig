<?php

namespace App\Http\Controllers;

use App\Helper\Image;
use App\Models\Quadrim;
use App\Models\Quadrimbrand;
use App\Models\Quadrimmake;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class AtvRimController extends Controller
{
    public string $model = 'Quadrim';

    protected int $cartQty = 1;

    protected function catalogDisplayTitle(string $brandTitle, string $treadTitle): string
    {
        $brand = trim($brandTitle);
        $tread = trim($treadTitle);

        if ($brand === '') {
            return $tread;
        }

        if (preg_match('/^(kvadru|kvadraciklu)\s+diski$/iu', $brand)) {
            return $tread !== '' ? $tread : $brand;
        }

        return trim($brand . ' ' . $tread);
    }

    /** @see RimsController::prepareRimsForList */
    protected function prepareQuadrimsForList(Collection $items): void
    {
        foreach ($items as $rim) {
            /** @var \App\Models\Quadrim $rim */
            $rim->includeStock = true;

            $brandTitle = (string) ($rim->brandTitle ?? '');
            $treadTitle = (string) ($rim->treadTitle ?? '');

            if ($brandTitle !== '') {
                $rim->setAttribute('brandTitle', $brandTitle);
            }
            if ($treadTitle !== '') {
                $rim->setAttribute('treadTitle', $treadTitle);
            }

            $displayTitle = $this->catalogDisplayTitle($brandTitle, $treadTitle);

            $rim->setAttribute('title', $displayTitle);
            $rim->setAttribute('fullTitle', $displayTitle);
            $rim->setAttribute(
                'fullName',
                trim($brandTitle . ' ' . $treadTitle . ' ' . $rim->skr . 'x' . $rim->pcd . ' R' . $rim->d3 . ' ' . $rim->d1 . 'J et' . $rim->et . ' ' . ($rim->color ?? ''))
            );

            $rim->setAttribute('getUrl', route('kvadracikla-disks', [
                Str::slug($brandTitle),
                strtolower(str_replace('/', '_', $treadTitle)),
                $rim->rim_id,
            ]));

            $rim->setAttribute('dotAvailable', $rim->getDotAvailableAttribute());
            $rim->setAttribute('stockAvailability', $rim->getStockAvailabilityAttribute());
            $rim->setAttribute('stockCount', $rim->getStockCount());
        }
    }

    protected function baseListingQuery(Request $request)
    {
        $currentPcd = $request->input('currentPcd');
        $currentPcd = ($currentPcd === null || $currentPcd === '' || $currentPcd === 'Visi') ? null : $currentPcd;

        return Quadrim::query()
            ->where('visible_users', '<>', 0)
            ->when($currentPcd !== null && $currentPcd !== '', static function ($q) use ($currentPcd) {
                $q->where('pcd', $currentPcd);
            })
            ->orderByRaw('cast(d3 as decimal(7,2)) ASC')
            ->orderByRaw('cast(d1 as decimal(7,2)) ASC')
            ->orderBy('price2', 'DESC');
    }

    public function index(Request $request)
    {
        $rims = $this->baseListingQuery($request)->paginate(20)->appends($request->query());
        $this->prepareQuadrimsForList($rims->getCollection());

        $pcdValues = Quadrim::query()
            ->where('visible_users', '<>', 0)
            ->whereNotNull('pcd')
            ->where('pcd', '<>', '')
            ->distinct()
            ->orderBy('pcd', 'ASC')
            ->pluck('pcd');

        $currentPcdForView = ($request->input('currentPcd') === '' || $request->input('currentPcd') === null)
            ? 'Visi'
            : $request->input('currentPcd');

        $brandsList = Quadrimbrand::query()->orderBy('b_title', 'ASC')->get();

        return view('atv-rims.index', [
            'rims' => $rims,
            'pcdValues' => $pcdValues,
            'currentPcdForView' => $currentPcdForView,
            'cartQty' => $this->cartQty,
            'brandsForSidebar' => $brandsList,
        ]);
    }

    public function search(Request $request)
    {
        return $this->index($request);
    }

    protected function resolveQuadrBrandFromSlug(string $brandParam): ?Quadrimbrand
    {
        $candidate = Quadrimbrand::where('b_title', $brandParam)->first();
        if ($candidate) {
            return $candidate;
        }

        $slugNeedle = strtolower($brandParam);

        foreach (Quadrimbrand::all() as $b) {
            if (Str::slug((string) $b->b_title) === $slugNeedle || strtolower((string) $b->b_title) === $slugNeedle) {
                return $b;
            }
        }

        return null;
    }

    /** URL tread segment mirrors quadrim blades: strtolower(str_replace('/', '_', t_title)). */
    protected function resolveQuadrMake(?Quadrimbrand $brand, string $treadSegment): ?Quadrimmake
    {
        if (!$brand) {
            return null;
        }

        $segment = strtolower($treadSegment);

        foreach (Quadrimmake::where('brand_id', $brand->brand_id)->get() as $make) {
            $canonical = strtolower(str_replace('/', '_', (string) $make->t_title));
            if ($canonical === $segment) {
                return $make;
            }
        }

        return null;
    }

    public function show(string $brand, string $tread, string $rim)
    {
        $brandModel = $this->resolveQuadrBrandFromSlug($brand);
        if (!$brandModel) {
            abort(404);
        }

        $makeRow = $this->resolveQuadrMake($brandModel, $tread);
        if (!$makeRow) {
            abort(404);
        }

        $rimId = (int) $rim;

        $rimsCollection = Quadrim::query()
            ->where('make_id', $makeRow->make_id)
            ->where('visible_users', '<>', 0)
            ->orderBy('price2', 'DESC')
            ->orderByRaw('cast(d3 as decimal(7,2)) ASC')
            ->orderByRaw('cast(d1 as decimal(7,2)) ASC')
            ->get();

        $currRim = $rimsCollection->firstWhere('rim_id', $rimId);

        if (!$currRim) {
            $currRim = Quadrim::query()
                ->where('rim_id', $rimId)
                ->where('make_id', $makeRow->make_id)
                ->first();

            if (!$currRim) {
                abort(404);
            }

            if (!$rimsCollection->contains('rim_id', $currRim->rim_id)) {
                $rimsCollection->push($currRim);
            }
        }

        $this->prepareQuadrimsForList($rimsCollection);

        $currModel = $rimsCollection->firstWhere('rim_id', $currRim->rim_id) ?: $currRim;
        $currModel->includeStock = true;
        $currModel->setAttribute('t_comment', $makeRow->t_comment ?? null);
        $currModel->setAttribute('b_comment', $brandModel->b_comment ?? null);

        return view('atv-rims.show', [
            'rims' => $rimsCollection,
            'currRim' => $currModel,
            'cartQty' => $this->cartQty,
        ]);
    }

    /** Session cart parity with RimsController::rims_ajax */
    public function ajax(Request $request): JsonResponse
    {
        $rimId = $request->rim_id ?? $request->tire_id ?? $request->id ?? null;

        if ($rimId === null || $rimId === '') {
            return response()->json([
                'error' => 'No rim ID provided',
                'received_data' => $request->all(),
            ], 400);
        }

        $rim = Quadrim::query()->where('rim_id', $rimId)->first();

        if (!$rim) {
            return response()->json(['error' => 'Rim not found with ID: ' . $rimId], 404);
        }

        $this->prepareQuadrimsForList(collect([$rim]));

        $quantityInput = $request->quantity ? (int) $request->quantity : $this->cartQty;
        $qty = max(1, $quantityInput);
        $cart = session()->get('cart', ['products' => []]);

        $brandTitle = $rim->brandTitle ?? '';
        $treadTitle = $rim->treadTitle ?? '';

        $rimUrl = route('kvadracikla-disks', [
            Str::slug($brandTitle),
            strtolower(str_replace('/', '_', (string) $treadTitle)),
            $rim->rim_id,
        ]);

        $cart['products'][$rim->rim_id] = array_merge($cart['products'][$rim->rim_id] ?? [], [
            'id' => $rim->rim_id,
            'name' => $this->catalogDisplayTitle((string) $brandTitle, (string) $treadTitle),
            'make_id' => $rim->make_id,
            'd1' => $rim->d1 ?? null,
            'd3' => $rim->d3 ?? null,
            'skr' => $rim->skr ?? null,
            'pcd' => $rim->pcd ?? null,
            'et' => $rim->et ?? null,
            'color' => $rim->color ?? null,
            'type' => 'Disks',
            'url' => $rimUrl,
            'image' => Image::image('quadr-rim', $rim->make_id),
            'price' => $rim->price2 ?? $rim->price1 ?? 0,
            'availability' => $rim->dotAvailable ?? 'green',
            'category' => $this->model,
        ]);

        $existingQty = isset($cart['products'][$rim->rim_id]['quantity']) ? (int) $cart['products'][$rim->rim_id]['quantity'] : 0;
        $cart['products'][$rim->rim_id]['quantity'] = $existingQty + $qty;

        $priceMap = collect($cart['products'] ?? [])->pluck('price', 'id')->all();
        $totalSum = 0;
        foreach ($cart['products'] as $product) {
            $totalSum += ($product['quantity'] ?? 0) * ($priceMap[$product['id']] ?? 0);
        }
        $cart['total_sum'] = $totalSum;
        session()->put('cart', $cart);
        ShopController::updateCartInDatabase($totalSum);

        return response()->json([
            'cart' => $cart,
            'total_sum' => $totalSum,
            'quantity' => array_sum(array_column($cart['products'], 'quantity')),
            'bought' => $qty,
        ]);
    }

    public function getBrands(): JsonResponse
    {
        $names = Quadrimbrand::query()
            ->orderBy('b_title', 'ASC')
            ->get()
            ->map(static fn (Quadrimbrand $b) => $b->b_title)
            ->filter()
            ->unique()
            ->values();

        return response()->json($names);
    }
}
