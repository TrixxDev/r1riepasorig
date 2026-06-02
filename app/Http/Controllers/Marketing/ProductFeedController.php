<?php

namespace App\Http\Controllers\Marketing;

use App\Helper\Image;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class ProductFeedController extends Controller
{
    public function googleXml(Request $request)
    {
        $token = config('marketing.product_feed.token');
        if (! empty($token) && $request->query('token') !== $token) {
            abort(403);
        }

        $limit = max(100, min((int) config('marketing.product_feed.batch_size'), 20000));
        $site = rtrim(config('app.url'), '/');

        $itemsXml = '';
        $itemsXml .= $this->buildAutoTireItems($site, $limit);
        $itemsXml .= $this->buildMotoTireItems($site, $limit);
        $itemsXml .= $this->buildQuadTireItems($site, $limit);
        $itemsXml .= $this->buildBigTireItems($site, $limit);
        $itemsXml .= $this->buildRimItems($site, $limit);

        $title = 'R1 Riepas — riepas un diski';

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'
            .'<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0">'
            .'<channel>'
            .'<title>'.self::xmlText($title).'</title>'
            .'<link>'.self::xmlText($site).'</link>'
            .'<description>'.self::xmlText($title).'</description>'
            .$itemsXml
            .'</channel>'
            .'</rss>';

        return Response::make($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }

    protected function buildAutoTireItems(string $site, int $limit): string
    {
        $rows = DB::table('auto_tires')
            ->join('auto_treads', 'auto_tires.make_id', '=', 'auto_treads.tread_id')
            ->join('auto_brands', 'auto_treads.brand_id', '=', 'auto_brands.brand_id')
            ->whereRaw('(COALESCE(auto_tires.urs_quantity,0) + COALESCE(auto_tires.krs_quantity,0)) > 0')
            ->orderBy('auto_tires.tire_id')
            ->limit($limit)
            ->select([
                'auto_tires.tire_id',
                'auto_tires.make_id',
                'auto_tires.d1', 'auto_tires.d2', 'auto_tires.d3',
                'auto_tires.price1', 'auto_tires.price2',
                'auto_tires.code', 'auto_tires.li', 'auto_tires.si',
                'auto_treads.t_title as tread_title',
                'auto_treads.season',
                'auto_brands.title as brand_title',
                'auto_brands.slug as brand_slug',
            ])
            ->get();

        $xml = '';
        foreach ($rows as $row) {
            $price = $this->resolvePrice($row->price1, $row->price2);
            if ($price <= 0) continue;

            $routeName = ((int) $row->season === 1) ? 'vasaras-riepa' : 'ziemas-riepa';
            $treadSegment = str_replace('/', '_', $row->tread_title);
            $productUrl = $this->safeRoute($routeName, [$row->brand_slug, $treadSegment, $row->tire_id], $site);
            if (! $productUrl) continue;

            $imageLink = $this->resolveImage('auto', $row->make_id);
            $productTitle = $row->brand_title.' '.$row->tread_title.' '.$row->d1.'/'.$row->d2.' R'.$row->d3.' '.$row->code.' '.$row->li.$row->si;

            $xml .= $this->buildItem('auto_'.$row->tire_id, $productTitle, $productUrl, $imageLink, $price, $row->brand_title, 'Transportlīdzekļi > Riepas');
        }

        return $xml;
    }

    protected function buildMotoTireItems(string $site, int $limit): string
    {
        $rows = DB::table('moto_tires')
            ->join('moto_treads', 'moto_tires.make_id', '=', 'moto_treads.tread_id')
            ->join('moto_brands', 'moto_treads.brand_id', '=', 'moto_brands.brand_id')
            ->whereRaw('(COALESCE(moto_tires.urs_quantity,0) + COALESCE(moto_tires.krs_quantity,0)) > 0')
            ->orderBy('moto_tires.tire_id')
            ->limit($limit)
            ->select([
                'moto_tires.tire_id',
                'moto_tires.make_id',
                'moto_tires.d1', 'moto_tires.d2', 'moto_tires.d3', 'moto_tires.d4',
                'moto_tires.price1', 'moto_tires.price2',
                'moto_tires.code', 'moto_tires.li', 'moto_tires.si',
                'moto_treads.title as tread_title',
                'moto_brands.title as brand_title',
            ])
            ->get();

        $xml = '';
        foreach ($rows as $row) {
            $price = $this->resolvePrice($row->price1, $row->price2);
            if ($price <= 0) continue;

            $brandLower = strtolower($row->brand_title);
            $treadSegment = str_replace('/', '_', $row->tread_title);
            $productUrl = $this->safeRoute('motociklu-riepa', [$brandLower, $treadSegment, $row->tire_id], $site);
            if (! $productUrl) continue;

            $imageLink = $this->resolveImage('moto', $row->make_id);
            $sizeStr = $row->d2 ? $row->d1.'/'.$row->d2.' '.$row->d4.' '.$row->d3 : $row->d1.' '.$row->d4.' '.$row->d3;
            $productTitle = $row->brand_title.' '.$row->tread_title.' '.$sizeStr.' '.$row->code.' '.$row->li.$row->si;

            $xml .= $this->buildItem('moto_'.$row->tire_id, $productTitle, $productUrl, $imageLink, $price, $row->brand_title, 'Transportlīdzekļi > Motociklu riepas');
        }

        return $xml;
    }

    protected function buildQuadTireItems(string $site, int $limit): string
    {
        $rows = DB::table('quadr_tires')
            ->join('quadr_treads', 'quadr_tires.make_id', '=', 'quadr_treads.tread_id')
            ->join('quadr_brands', 'quadr_treads.brand_id', '=', 'quadr_brands.brand_id')
            ->whereRaw('(COALESCE(quadr_tires.urs_quantity,0) + COALESCE(quadr_tires.krs_quantity,0)) > 0')
            ->orderBy('quadr_tires.tire_id')
            ->limit($limit)
            ->select([
                'quadr_tires.tire_id',
                'quadr_tires.make_id',
                'quadr_tires.d1', 'quadr_tires.d2', 'quadr_tires.d3',
                'quadr_tires.price1', 'quadr_tires.price2',
                'quadr_tires.code', 'quadr_tires.li', 'quadr_tires.si',
                'quadr_treads.t_title as tread_title',
                'quadr_brands.b_title as brand_title',
                'quadr_brands.slug as brand_slug',
            ])
            ->get();

        $xml = '';
        foreach ($rows as $row) {
            $price = $this->resolvePrice($row->price1, $row->price2);
            if ($price <= 0) continue;

            $treadSegment = str_replace('/', '_', $row->tread_title);
            $productUrl = $this->safeRoute('kvadraciklu-riepa', [$row->brand_slug, $treadSegment, $row->tire_id], $site);
            if (! $productUrl) continue;

            $imageLink = $this->resolveImage('quadr', $row->make_id);
            $productTitle = $row->brand_title.' '.$row->tread_title.' '.$row->d1.'/'.$row->d2.' R'.$row->d3.' '.$row->code.' '.$row->li.$row->si;

            $xml .= $this->buildItem('quadr_'.$row->tire_id, $productTitle, $productUrl, $imageLink, $price, $row->brand_title, 'Transportlīdzekļi > Kvadraciklu riepas');
        }

        return $xml;
    }

    protected function buildBigTireItems(string $site, int $limit): string
    {
        $rows = DB::table('big_tires')
            ->join('bigtire_treads', 'big_tires.make_id', '=', 'bigtire_treads.tread_id')
            ->join('bigtire_brands', 'bigtire_treads.brand_id', '=', 'bigtire_brands.brand_id')
            ->whereRaw('(COALESCE(big_tires.urs_quantity,0) + COALESCE(big_tires.krs_quantity,0)) > 0')
            ->orderBy('big_tires.tire_id')
            ->limit($limit)
            ->select([
                'big_tires.tire_id',
                'big_tires.make_id',
                'big_tires.d1', 'big_tires.d2', 'big_tires.d3',
                'big_tires.price1', 'big_tires.price2',
                'big_tires.code', 'big_tires.li', 'big_tires.si',
                'bigtire_treads.title as tread_title',
                'bigtire_brands.title as brand_title',
                'bigtire_brands.slug as brand_slug',
            ])
            ->get();

        $xml = '';
        foreach ($rows as $row) {
            $price = $this->resolvePrice($row->price1, $row->price2);
            if ($price <= 0) continue;

            $treadSegment = str_replace('/', '_', $row->tread_title);
            $productUrl = $this->safeRoute('lielas-riepa', [$row->brand_slug, $treadSegment, $row->tire_id], $site);
            if (! $productUrl) continue;

            $imageLink = $this->resolveImage('big', $row->make_id);
            $productTitle = $row->brand_title.' '.$row->tread_title.' '.$row->d1.'/'.$row->d2.' R'.$row->d3.' '.$row->code.'PR '.$row->li.$row->si;

            $xml .= $this->buildItem('big_'.$row->tire_id, $productTitle, $productUrl, $imageLink, $price, $row->brand_title, 'Transportlīdzekļi > Industriālās riepas');
        }

        return $xml;
    }

    protected function buildRimItems(string $site, int $limit): string
    {
        $rows = DB::table('rims')
            ->join('rim_makes', 'rims.make_id', '=', 'rim_makes.make_id')
            ->join('rim_brands', 'rim_makes.brand_id', '=', 'rim_brands.brand_id')
            ->whereRaw('(COALESCE(rims.urs_quantity,0) + COALESCE(rims.krs_quantity,0)) > 0')
            ->orderBy('rims.rim_id')
            ->limit($limit)
            ->select([
                'rims.rim_id',
                'rims.make_id',
                'rims.d1', 'rims.d3', 'rims.skr', 'rims.pcd',
                'rims.et', 'rims.dc', 'rims.color',
                'rims.price3',
                'rim_makes.title as make_title',
                'rim_brands.title as brand_title',
            ])
            ->get();

        $xml = '';
        foreach ($rows as $row) {
            $price = (float) ($row->price3 ?? 0);
            if ($price <= 0) continue;

            $makeSegment = str_replace('/', '_', $row->make_title);
            $productUrl = $this->safeRoute('lietais-disks', [$row->brand_title, $makeSegment, $row->rim_id], $site);
            if (! $productUrl) continue;

            $imageLink = $this->resolveImage('auto-rim', $row->make_id);
            $productTitle = $row->brand_title.' '.$row->make_title.' '.$row->skr.'x'.$row->pcd.' R'.$row->d3.' '.$row->d1.'J et'.$row->et.' '.$row->dc.' '.$row->color;

            $xml .= $this->buildItem('rim_'.$row->rim_id, $productTitle, $productUrl, $imageLink, $price, $row->brand_title, 'Transportlīdzekļi > Diski');
        }

        return $xml;
    }

    protected function buildItem(string $id, string $title, string $url, string $imageLink, float $price, string $brand, string $productType): string
    {
        return '<item>'
            .'<g:id>'.self::xmlText($id).'</g:id>'
            .'<g:title>'.self::xmlText($title).'</g:title>'
            .'<g:description>'.self::xmlText($title).'</g:description>'
            .'<g:link>'.self::xmlText($url).'</g:link>'
            .'<g:image_link>'.self::xmlText($imageLink).'</g:image_link>'
            .'<g:condition>new</g:condition>'
            .'<g:availability>in stock</g:availability>'
            .'<g:price>'.self::xmlText(number_format($price, 2, '.', '')).' EUR</g:price>'
            .'<g:brand>'.self::xmlText($brand).'</g:brand>'
            .'<g:product_type>'.self::xmlText($productType).'</g:product_type>'
            .'</item>';
    }

    protected function resolvePrice($price1, $price2): float
    {
        return ($price2 !== null && $price2 !== '') ? (float) $price2 : (float) $price1;
    }

    protected function resolveImage(string $type, $makeId): string
    {
        $imageLink = Image::showAd($type, $makeId);
        if (! $imageLink || strpos((string) $imageLink, 'http') !== 0) {
            $imageLink = asset('img/p/r1-logo.svg');
        }

        return $imageLink;
    }

    protected function safeRoute(string $routeName, array $params, string $site): ?string
    {
        try {
            $url = route($routeName, $params);
        } catch (\Throwable $e) {
            return null;
        }
        if (! preg_match('#^https?://#', $url)) {
            $url = $site.'/'.ltrim($url, '/');
        }

        return $url;
    }

    private static function xmlText(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
