<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddSiteSeasonSetting extends Migration
{
    public function up()
    {
        if (DB::table('cart_config')->where('name', 'site_season')->exists()) {
            return;
        }

        $season = (int) env('SEASON', 2);
        if (! in_array($season, [1, 2], true)) {
            $season = 2;
        }

        DB::table('cart_config')->insert([
            'name' => 'site_season',
            'abbr' => 'Sezona',
            'value' => (string) $season,
        ]);
    }

    public function down()
    {
        DB::table('cart_config')->where('name', 'site_season')->delete();
    }
}
