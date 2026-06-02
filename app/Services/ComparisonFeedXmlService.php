<?php

namespace App\Services;

use App\Helper\Image;
use App\Models\Autotire;
use App\Models\Bigtire;
use App\Models\Moto;
use App\Models\Quadr;
use App\Models\Rim;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ComparisonFeedXmlService
{
    private string $baseUrl;

    /** @var array<string, string> */
    private array $imageCache = [];

    /** @var array<string, string> */
    private array $routes = [];

    public function serveSalidzini(Request $request): Response
    {
        set_time_limit(0);

        return $this->serve(
            $request,
            $this->salidziniPath(),
            fn () => $this->buildSalidzini()
        );
    }

    public function serveKurpirkt(Request $request): Response
    {
        set_time_limit(0);

        return $this->serve(
            $request,
            $this->kurpirktPath(),
            fn () => $this->buildKurpirkt()
        );
    }

    private function serve(Request $request, string $path, callable $builder): Response
    {
        if (!$request->boolean('refresh') && $this->isCacheFresh($path)) {
            return response()->file($path, [
                'Content-Type' => 'application/xml; charset=UTF-8',
            ]);
        }

        $xml = $builder();
        File::ensureDirectoryExists(dirname($path));
        file_put_contents($path, $xml);

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }

    public function buildSalidzini(): string
    {
        $this->bootContext();

        $items = [];
        $this->appendAutoSalidzini($items);
        $this->appendMotoSalidzini($items);
        $this->appendQuadrSalidzini($items);
        $this->appendBigSalidzini($items);
        $this->appendRimSalidzini($items);

        return $this->wrapRoot($items);
    }

    public function buildKurpirkt(): string
    {
        $this->bootContext();

        $items = [];
        $this->appendAutoKurpirkt($items);
        $this->appendMotoKurpirkt($items);
        $this->appendQuadrKurpirkt($items);
        $this->appendRimKurpirkt($items);

        return $this->wrapRoot($items);
    }

    private function bootContext(): void
    {
        $this->baseUrl = rtrim((string) config('app.url'), '/');
        $this->imageCache = [];
        $this->routes = [
            'vasaras_riepas' => route('vasaras-riepas'),
            'ziemas_riepas' => route('ziemas-riepas'),
            'motociklu_riepas' => route('motociklu-riepas'),
            'kvadraciklu_riepas' => route('kvadraciklu-riepas'),
            'lielas_riepas' => route('lielas-riepas'),
            'lietie_diski' => route('lietie-diski'),
        ];
    }

    private function salidziniPath(): string
    {
        return public_path('storage/xml/salidzini.xml');
    }

    private function kurpirktPath(): string
    {
        return public_path('storage/xml/kurpirkt.xml');
    }

    private function cacheTtl(): int
    {
        return max(0, (int) config('marketing.comparison_feed.cache_seconds', 3600));
    }

    private function isCacheFresh(string $path): bool
    {
        $ttl = $this->cacheTtl();

        return $ttl > 0
            && is_file($path)
            && (time() - (int) filemtime($path)) < $ttl;
    }

    /**
     * @param  array<int, string>  $items
     */
    private function wrapRoot(array $items): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
            . '<root>' . implode('', $items) . '</root>' . "\n";
    }

    /**
     * @param  array<int, string>  $items
     * @param  array<string, scalar|null>  $fields
     */
    private function appendItem(array &$items, array $fields): void
    {
        $xml = '<item>';
        foreach ($fields as $tag => $value) {
            if ($value === null || $value === false || $value === '') {
                continue;
            }
            $xml .= '<' . $tag . '>' . $this->xmlText((string) $value) . '</' . $tag . '>';
        }
        $items[] = $xml . '</item>';
    }

    private function xmlText(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1, 'UTF-8');
    }

    private function offerPrice(object $row): float
    {
        $price2 = $row->price2 ?? null;

        return (float) (($price2 === null || $price2 === '') ? $row->price1 : $price2);
    }

    private function inStock(object $row, int $partnerStock): int
    {
        return (int) $row->quantity + $partnerStock;
    }

    private function feedImage(string $type, $makeId): string
    {
        $key = $type . ':' . $makeId;
        if (isset($this->imageCache[$key])) {
            return $this->imageCache[$key];
        }

        $relative = str_replace(base_path(), '', Image::image($type, $makeId));
        $relative = ltrim(str_replace('\\', '/', $relative), '/');

        if (Image::exists($type, $makeId)) {
            $absolutePath = Image::image($type, $makeId);
            if (file_exists(str_replace('.jpg', '.png', $absolutePath))) {
                $relative = str_replace('.jpg', '.png', $relative);
            }
            $url = $this->baseUrl . '/' . $relative;
        } else {
            $url = $this->baseUrl . '/img/p/r1-logo.svg';
        }

        return $this->imageCache[$key] = $url;
    }

    private function loadAutoTreadMeta(): Collection
    {
        return DB::table('auto_treads')
            ->join('auto_brands', 'auto_treads.brand_id', '=', 'auto_brands.brand_id')
            ->select([
                'auto_treads.tread_id',
                'auto_treads.season',
                'auto_treads.t_title',
                'auto_brands.slug',
                'auto_brands.title as brand_title',
            ])
            ->get()
            ->keyBy('tread_id');
    }

    private function loadMotoTreadMeta(): Collection
    {
        return DB::table('moto_treads')
            ->join('moto_brands', 'moto_treads.brand_id', '=', 'moto_brands.brand_id')
            ->select([
                'moto_treads.tread_id',
                'moto_treads.title as tread_title',
                'moto_brands.title as brand_title',
            ])
            ->get()
            ->keyBy('tread_id');
    }

    private function loadQuadrTreadMeta(): Collection
    {
        return DB::table('quadr_treads')
            ->join('quadr_brands', 'quadr_treads.brand_id', '=', 'quadr_brands.brand_id')
            ->select([
                'quadr_treads.tread_id',
                'quadr_treads.t_title',
                'quadr_brands.slug',
                'quadr_brands.b_title as brand_title',
            ])
            ->get()
            ->keyBy('tread_id');
    }

    private function loadBigTreadMeta(): Collection
    {
        return DB::table('bigtire_treads')
            ->join('bigtire_brands', 'bigtire_treads.brand_id', '=', 'bigtire_brands.brand_id')
            ->select([
                'bigtire_treads.tread_id',
                'bigtire_treads.title',
                'bigtire_brands.slug',
                'bigtire_brands.title as brand_title',
            ])
            ->get()
            ->keyBy('tread_id');
    }

    private function preloadAutoStock(): void
    {
        $ids = Autotire::query()->where('visible_users', '<>', 0)->pluck('tire_id')->all();
        Autotire::preloadStockData($ids);
    }

    private function preloadMotoStock(): void
    {
        $ids = Moto::query()->where('visible_users', '<>', 0)->pluck('tire_id')->all();
        Moto::preloadStockData($ids);
    }

    private function preloadQuadrStock(): void
    {
        $ids = Quadr::query()->where('visible_users', '<>', 0)->pluck('tire_id')->all();
        Quadr::preloadStockData($ids);
    }

    private function preloadBigStock(): void
    {
        $ids = Bigtire::query()->where('visible_users', '<>', 0)->pluck('tire_id')->all();
        Bigtire::preloadStockData($ids);
    }

    private function preloadRimStock(): void
    {
        $ids = Rim::query()->where('visible_users', '<>', 0)->pluck('rim_id')->all();
        Rim::preloadStockData($ids);
        $makeIds = Rim::query()->where('visible_users', '<>', 0)->pluck('make_id')->unique()->filter()->all();
        Rim::preloadMakeData($makeIds);
    }

    private function autoProductLink(object $meta, int $tireId): string
    {
        $tread = str_replace('/', '_', (string) $meta->t_title);

        if ((int) $meta->season === 1) {
            return route('vasaras-riepa', [$meta->slug, $tread, $tireId]);
        }

        return route('ziemas-riepa', [$meta->slug, $tread, $tireId]);
    }

    private function autoFullName(object $tire, object $meta): string
    {
        return trim($meta->brand_title . ' ' . $meta->t_title . ' ' . $tire->d1 . '/' . $tire->d2 . ' R' . $tire->d3 . ' ' . $tire->code . ' ' . $tire->li . $tire->si);
    }

    private function motoProductLink(object $meta, int $tireId): string
    {
        return route('motociklu-riepa', [
            strtolower((string) $meta->brand_title),
            str_replace('/', '_', (string) $meta->tread_title),
            $tireId,
        ]);
    }

    private function motoFullName(object $tire, object $meta): string
    {
        $size = $tire->d2 !== '' && $tire->d2 !== null
            ? $tire->d1 . '/' . $tire->d2 . ' ' . $tire->d4 . ' ' . $tire->d3
            : $tire->d1 . ' ' . $tire->d4 . ' ' . $tire->d3;

        return trim($meta->brand_title . ' ' . $meta->tread_title . ' ' . $size . ' ' . $tire->code . ' ' . $tire->li . $tire->si);
    }

    /**
     * @param  array<int, string>  $items
     */
    private function appendAutoSalidzini(array &$items): void
    {
        $metaByTread = $this->loadAutoTreadMeta();
        $this->preloadAutoStock();

        foreach (Autotire::query()->where('visible_users', '<>', 0)->cursor() as $tire) {
            $meta = $metaByTread->get($tire->make_id);
            if (!$meta || !isset($meta->season)) {
                continue;
            }

            $price = $this->offerPrice($tire);
            if ($price <= 0) {
                continue;
            }

            $link = $this->autoProductLink($meta, (int) $tire->tire_id);
            if ($link === '') {
                continue;
            }

            $stock = $this->inStock($tire, $tire->getStockCount());
            $fields = [
                'name' => $this->autoFullName($tire, $meta),
                'price' => $price,
                'link' => $link,
                'image' => $this->feedImage('auto', $tire->make_id),
                'in_stock' => $stock,
                'category_link' => (int) $meta->season === 1 ? $this->routes['vasaras_riepas'] : $this->routes['ziemas_riepas'],
            ];

            if ((int) $meta->season === 1) {
                $fields['category'] = 'Vasaras riepas >> R' . $tire->d3;
                $fields['category_full'] = 'Auto preces >> Vasaras riepas >> R' . $tire->d3;
            } else {
                $fields['category'] = 'Ziemas riepas >> R' . $tire->d3;
                $fields['category_full'] = 'Auto preces >> Ziemas riepas >> R' . $tire->d3;
            }

            $this->appendItem($items, $fields);
        }

        Autotire::clearStockCache();
    }

    /**
     * @param  array<int, string>  $items
     */
    private function appendMotoSalidzini(array &$items): void
    {
        $metaByTread = $this->loadMotoTreadMeta();
        $this->preloadMotoStock();

        foreach (Moto::query()->where('visible_users', '<>', 0)->cursor() as $tire) {
            $meta = $metaByTread->get($tire->make_id);
            if (!$meta || empty($meta->brand_title) || empty($meta->tread_title)) {
                continue;
            }

            $price = $this->offerPrice($tire);
            if ($price <= 0) {
                continue;
            }

            $link = $this->motoProductLink($meta, (int) $tire->tire_id);
            $this->appendItem($items, [
                'name' => $this->motoFullName($tire, $meta),
                'price' => $price,
                'link' => $link,
                'image' => $this->feedImage('moto', $tire->make_id),
                'category' => 'Motociklu riepas',
                'category_full' => 'Auto preces >> Motociklu riepas',
                'category_link' => $this->routes['motociklu_riepas'],
                'in_stock' => $this->inStock($tire, $tire->getStockCount()),
            ]);
        }

        Moto::clearStockCache();
    }

    /**
     * @param  array<int, string>  $items
     */
    private function appendQuadrSalidzini(array &$items): void
    {
        $metaByTread = $this->loadQuadrTreadMeta();
        $this->preloadQuadrStock();

        foreach (Quadr::query()->where('visible_users', '<>', 0)->cursor() as $tire) {
            $meta = $metaByTread->get($tire->make_id);
            if (!$meta || empty($meta->slug) || empty($meta->t_title)) {
                continue;
            }

            $price = $this->offerPrice($tire);
            if ($price <= 0) {
                continue;
            }

            $link = str_replace('&', '$1', route('kvadraciklu-riepa', [
                $meta->slug,
                str_replace('/', '_', (string) $meta->t_title),
                $tire->tire_id,
            ]));

            $this->appendItem($items, [
                'name' => $tire->fullName,
                'price' => $price,
                'link' => $link,
                'image' => $this->feedImage('quadr', $tire->make_id),
                'category' => 'Kvadraciklu riepas',
                'category_full' => 'Auto preces >> Kvadraciklu riepas',
                'category_link' => $this->routes['kvadraciklu_riepas'],
                'in_stock' => $this->inStock($tire, $tire->getStockCount()),
            ]);
        }

        Quadr::clearStockCache();
    }

    /**
     * @param  array<int, string>  $items
     */
    private function appendBigSalidzini(array &$items): void
    {
        $metaByTread = $this->loadBigTreadMeta();
        $this->preloadBigStock();

        foreach (Bigtire::query()->where('visible_users', '<>', 0)->cursor() as $tire) {
            $meta = $metaByTread->get($tire->make_id);
            if (!$meta || empty($meta->slug) || empty($meta->title)) {
                continue;
            }

            $price = $this->offerPrice($tire);
            if ($price <= 0) {
                continue;
            }

            $this->appendItem($items, [
                'name' => $tire->fullName,
                'price' => $price,
                'link' => route('lielas-riepa', [$meta->slug, str_replace('/', '_', (string) $meta->title), $tire->tire_id]),
                'image' => $this->feedImage('big', $tire->make_id),
                'category' => 'Industriālās riepas',
                'category_full' => 'Auto preces >> Industriālās riepas',
                'category_link' => $this->routes['lielas_riepas'],
                'in_stock' => $this->inStock($tire, $tire->getStockCount()),
            ]);
        }

        Bigtire::clearStockCache();
    }

    /**
     * @param  array<int, string>  $items
     */
    private function appendRimSalidzini(array &$items): void
    {
        $this->preloadRimStock();

        foreach (Rim::query()->where('visible_users', '<>', 0)->cursor() as $rim) {
            $link = $rim->link;
            if ($link === false || $link === '') {
                continue;
            }

            $price = (float) $rim->offerPrice;
            if ($price <= 0) {
                continue;
            }

            $this->appendItem($items, [
                'name' => $rim->fullName,
                'price' => $price,
                'link' => $link,
                'image' => $this->feedImage('auto-rim', $rim->make_id),
                'category' => 'Lietie diski',
                'category_full' => ' >> ' . $rim->d3 . '"',
                'category_link' => $this->routes['lietie_diski'],
                'in_stock' => $this->inStock($rim, $rim->getStockCount()),
            ]);
        }

        Rim::clearStockCache();
    }

    /**
     * @param  array<int, string>  $items
     */
    private function appendAutoKurpirkt(array &$items): void
    {
        $metaByTread = $this->loadAutoTreadMeta();
        $this->preloadAutoStock();

        foreach (Autotire::query()->where('visible_users', '<>', 0)->cursor() as $tire) {
            $meta = $metaByTread->get($tire->make_id);
            if (!$meta || !isset($meta->season)) {
                continue;
            }

            $price = $this->offerPrice($tire);
            if ($price <= 0) {
                continue;
            }

            $link = $this->autoProductLink($meta, (int) $tire->tire_id);
            $fields = [
                'name' => $this->autoFullName($tire, $meta),
                'price' => $price,
                'link' => $link,
                'image' => $this->feedImage('auto', $tire->make_id),
                'in_stock' => $this->inStock($tire, $tire->getStockCount()),
                'category_link' => (int) $meta->season === 1 ? $this->routes['vasaras_riepas'] : $this->routes['ziemas_riepas'],
            ];

            if ((int) $meta->season === 1) {
                $fields['category'] = 'Vasaras riepas';
                $fields['category_full'] = 'Auto piederumi > Vasaras riepas > R' . $tire->d3;
            } else {
                $fields['category'] = 'Ziemas riepas';
                $fields['category_full'] = 'Auto piederumi > Ziemas riepas > R' . $tire->d3;
            }

            $this->appendItem($items, $fields);
        }

        Autotire::clearStockCache();
    }

    /**
     * @param  array<int, string>  $items
     */
    private function appendMotoKurpirkt(array &$items): void
    {
        $metaByTread = $this->loadMotoTreadMeta();
        $this->preloadMotoStock();

        foreach (Moto::query()->where('visible_users', '<>', 0)->cursor() as $tire) {
            $meta = $metaByTread->get($tire->make_id);
            if (!$meta || empty($meta->brand_title) || empty($meta->tread_title)) {
                continue;
            }

            $price = $this->offerPrice($tire);
            if ($price <= 0) {
                continue;
            }

            $this->appendItem($items, [
                'name' => $this->motoFullName($tire, $meta),
                'price' => $price,
                'link' => $this->motoProductLink($meta, (int) $tire->tire_id),
                'image' => $this->feedImage('moto', $tire->make_id),
                'category' => 'Motociklu riepas',
                'category_full' => 'Auto preces > Motociklu riepas',
                'category_link' => $this->routes['motociklu_riepas'],
                'in_stock' => $this->inStock($tire, $tire->getStockCount()),
            ]);
        }

        Moto::clearStockCache();
    }

    /**
     * @param  array<int, string>  $items
     */
    private function appendQuadrKurpirkt(array &$items): void
    {
        $metaByTread = $this->loadQuadrTreadMeta();
        $this->preloadQuadrStock();

        foreach (Quadr::query()->where('visible_users', '<>', 0)->cursor() as $tire) {
            $meta = $metaByTread->get($tire->make_id);
            if (!$meta || empty($meta->slug) || empty($meta->t_title)) {
                continue;
            }

            $price = $this->offerPrice($tire);
            if ($price <= 0) {
                continue;
            }

            $this->appendItem($items, [
                'name' => $tire->fullName,
                'price' => $price,
                'link' => str_replace('&', '$1', route('kvadraciklu-riepa', [
                    $meta->slug,
                    str_replace('/', '_', (string) $meta->t_title),
                    $tire->tire_id,
                ])),
                'image' => $this->feedImage('quadr', $tire->make_id),
                'category' => 'Kvadraciklu riepas',
                'category_full' => 'Auto piederumi > Kvadraciklu riepas',
                'category_link' => $this->routes['kvadraciklu_riepas'],
                'in_stock' => $this->inStock($tire, $tire->getStockCount()),
            ]);
        }

        Quadr::clearStockCache();
    }

    /**
     * @param  array<int, string>  $items
     */
    private function appendRimKurpirkt(array &$items): void
    {
        $this->preloadRimStock();

        foreach (Rim::query()->where('visible_users', '<>', 0)->cursor() as $rim) {
            $link = $rim->link;
            if ($link === false || $link === '') {
                continue;
            }

            $price = (float) $rim->offerPrice;
            if ($price <= 0) {
                continue;
            }

            $this->appendItem($items, [
                'name' => $rim->fullName,
                'price' => $price,
                'link' => $link,
                'image' => $this->feedImage('auto-rim', $rim->make_id),
                'category' => 'Lietie diski',
                'category_full' => ' >> ' . $rim->d3 . '"',
                'category_link' => $this->routes['lietie_diski'],
                'in_stock' => $this->inStock($rim, $rim->getStockCount()),
            ]);
        }

        Rim::clearStockCache();
    }
}
