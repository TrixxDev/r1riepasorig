<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class SiteSeasonService
{
    public const CONFIG_NAME = 'site_season';
    public const CACHE_KEY = 'r1_site_season';
    public const CACHE_TTL = 3600;
    public const SUMMER = 1;
    public const WINTER = 2;

    public function current(): int
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            $row = DB::table('cart_config')->where('name', self::CONFIG_NAME)->first();

            if ($row !== null) {
                return $this->normalize((int) $row->value);
            }

            return $this->normalize((int) config('site.default_season', self::WINTER));
        });
    }

    public function set(int $season): void
    {
        $season = $this->normalize($season);
        $existing = DB::table('cart_config')->where('name', self::CONFIG_NAME)->first();

        if ($existing !== null) {
            DB::table('cart_config')->where('id', $existing->id)->update([
                'value' => (string) $season,
            ]);
        } else {
            DB::table('cart_config')->insert([
                'name' => self::CONFIG_NAME,
                'abbr' => 'Sezona',
                'value' => (string) $season,
            ]);
        }

        Cache::forget(self::CACHE_KEY);
        Config::set('site.season', $season);
    }

    public function isSummer(): bool
    {
        return $this->current() === self::SUMMER;
    }

    public function isWinter(): bool
    {
        return $this->current() === self::WINTER;
    }

    protected function normalize(int $season): int
    {
        return in_array($season, [self::SUMMER, self::WINTER], true)
            ? $season
            : self::WINTER;
    }
}
