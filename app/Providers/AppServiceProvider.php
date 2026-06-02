<?php

namespace App\Providers;

use App\Models\Bannerimage;
use App\Services\CartService;
use App\Services\SiteSeasonService;
use App\Support\AgentDebugLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    private const BANNERS_CACHE_KEY = 'r1_view_shared_banners';
    private const BANNERS_CACHE_TTL = 900;
    private const CART_CONFIG_CACHE_KEY = 'r1_cart_config_options';
    private const CART_CONFIG_CACHE_TTL = 3600;

    public function register()
    {
        $this->app->singleton(CartService::class, function ($app) {
            return new CartService();
        });

        $this->app->singleton(SiteSeasonService::class, function ($app) {
            return new SiteSeasonService();
        });
    }

    public function boot()
    {
        Auth::guard('web')->setRememberDuration((int) config('auth.remember_minutes', 525600));

        $bootT0 = microtime(true);
        $this->registerSlowQueryListener();

        $t = microtime(true);
        $this->registerAuditConstants();
        $this->registerAppConstants();
        AgentDebugLog::write('A', 'AppServiceProvider.php:boot', 'boot_constants_ms', [
            'ms' => (int) round((microtime(true) - $t) * 1000),
        ]);

        $t = microtime(true);
        try {
            $banners = $this->getSharedBanners();
        } catch (\Throwable $e) {
            report($e);
            $banners = collect();
        }
        AgentDebugLog::write('A', 'AppServiceProvider.php:boot', 'boot_banners_ms', [
            'ms' => (int) round((microtime(true) - $t) * 1000),
            'count' => is_countable($banners) ? count($banners) : null,
            'cache_hit' => Cache::has(self::BANNERS_CACHE_KEY),
        ]);
        View::share('banners', $banners);

        $t = microtime(true);
        try {
            $this->hydrateCartConfigFromCache();
        } catch (\Throwable $e) {
            report($e);
        }
        AgentDebugLog::write('A', 'AppServiceProvider.php:boot', 'boot_cart_config_ms', [
            'ms' => (int) round((microtime(true) - $t) * 1000),
            'cache_hit' => Cache::has(self::CART_CONFIG_CACHE_KEY),
        ]);

        $t = microtime(true);
        try {
            $this->hydrateSiteSeason();
        } catch (\Throwable $e) {
            report($e);
            Config::set('site.season', (int) config('site.default_season', SiteSeasonService::WINTER));
        }
        AgentDebugLog::write('A', 'AppServiceProvider.php:boot', 'boot_site_season_ms', [
            'ms' => (int) round((microtime(true) - $t) * 1000),
            'cache_hit' => Cache::has(SiteSeasonService::CACHE_KEY),
        ]);

        $this->registerBuilderMacros();
        $this->registerPaginatorDefaults();
        $this->registerClientIpConstant();
        $this->queuePersistentSessionCookie();
        $this->registerCartCountViewComposer();

        AgentDebugLog::write('A', 'AppServiceProvider.php:boot', 'boot_total_ms', [
            'ms' => (int) round((microtime(true) - $bootT0) * 1000),
            'cache_driver' => config('cache.default'),
        ]);
    }

    protected function registerSlowQueryListener(): void
    {
        DB::listen(function ($query) {
            try {
                $req = request();
                if ($req && ! $req->attributes->get('_agent_first_query_logged')) {
                    $req->attributes->set('_agent_first_query_logged', true);
                    AgentDebugLog::write('F', 'AppServiceProvider.php:db_listen', 'first_query', [
                        'ms' => (int) round($query->time),
                        'sql_prefix' => substr(preg_replace('/\s+/', ' ', $query->sql), 0, 80),
                    ]);
                }
            } catch (\Throwable $e) {
                // ignore
            }

            $ms = $query->time;
            if ($ms < 500) {
                return;
            }
            $sql = preg_replace('/\s+/', ' ', $query->sql);
            AgentDebugLog::write('C', 'AppServiceProvider.php:db_listen', 'slow_query', [
                'ms' => (int) round($ms),
                'sql_prefix' => substr($sql, 0, 120),
            ]);
        });
    }

    protected function registerAuditConstants(): void
    {
        if (defined('AUDIT_FACILITY_LOGIN')) {
            return;
        }

        define('AUDIT_FACILITY_LOGIN', 100);
        define('AUDIT_FACILITY_USER', 101);
        define('AUDIT_FACILITY_DB', 102);
        define('AUDIT_FACILITY_SYSCORE', 103);
        define('AUDIT_FACILITY_MESSAGE', 104);
        define('AUDIT_FACILITY_DOCUMENT', 105);
        define('AUDIT_FACILITY_CATEGORY', 105);
        define('AUDIT_FACILITY_FIZPERS', 1);
        define('AUDIT_FACILITY_JURIDPERS', 2);
        define('AUDIT_SEVERITY_CRITICAL', 1);
        define('AUDIT_SEVERITY_WARNING', 2);
        define('AUDIT_SEVERITY_INFO', 3);
        define('AUDIT_SEVERITY_ERROR', 4);
        define('AUDIT_SEVERITY_DEBUG', 100);
    }

    protected function registerAppConstants(): void
    {
        if (! defined('TIRE_SEASON_SUMMER')) {
            define('TIRE_SEASON_SUMMER', 1);
            define('TIRE_SEASON_WINTER', 2);
            define('SLOT_STATUS_FREE', 0);
            define('SLOT_STATUS_TAKEN', 1);
            define('SLOT_STATUS_OFFER', 2);
            define('SLOT_STATUS_CLOSED', 3);
        }

        date_default_timezone_set('Europe/Riga');
    }

    protected function getSharedBanners()
    {
        return Cache::remember(self::BANNERS_CACHE_KEY, self::BANNERS_CACHE_TTL, function () {
            return Bannerimage::all();
        });
    }

    protected function hydrateCartConfigFromCache(): void
    {
        $options = Cache::remember(self::CART_CONFIG_CACHE_KEY, self::CART_CONFIG_CACHE_TTL, function () {
            return DB::table('cart_config')->get();
        });

        foreach ($options as $option) {
            if ($option->name === SiteSeasonService::CONFIG_NAME) {
                continue;
            }

            Config::set('app.settings.'.$option->name, (int) $option->value);
        }
    }

    protected function hydrateSiteSeason(): void
    {
        Config::set('site.season', app(SiteSeasonService::class)->current());
    }

    protected function registerBuilderMacros(): void
    {
        Builder::macro('whereLike', function ($attributes, $terms) {
            $this->where(function ($query) use ($attributes, $terms) {
                foreach (Arr::wrap($attributes) as $attribute) {
                    foreach (Arr::wrap($terms) as $term) {
                        if ($term == 'CURRYEAR') {
                            $query->orWhere($attribute, 'LIKE', '%'.$term.'%');
                            $query->orWhere($attribute, 'LIKE', 'DOT%'.substr(date('Y'), -2));
                        }
                        $query->orWhere($attribute, 'LIKE', '%'.$term.'%');
                    }
                }
            });

            return $this;
        });
    }

    protected function registerPaginatorDefaults(): void
    {
        Paginator::defaultView('vendor.pagination.custom');
        Paginator::defaultSimpleView('vendor.pagination.custom');
    }

    protected function registerClientIpConstant(): void
    {
        if (defined('user_ip')) {
            return;
        }

        if (! empty($_SERVER['HTTP_CLIENT_IP'])) {
            define('user_ip', $_SERVER['HTTP_CLIENT_IP']);
        } elseif (! empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            define('user_ip', $_SERVER['HTTP_X_FORWARDED_FOR']);
        } elseif (! empty($_SERVER['REMOTE_ADDR'])) {
            define('user_ip', $_SERVER['REMOTE_ADDR']);
        }
    }

    protected function queuePersistentSessionCookie(): void
    {
        if (! Cookie::has('persistent_session_id')) {
            Cookie::queue('persistent_session_id', session()->getId(), 43200);
        }
    }

    protected function registerCartCountViewComposer(): void
    {
        View::composer('*', function ($view) {
            $view->with('cartCount', app(CartService::class)->getCount());
        });
    }
}
