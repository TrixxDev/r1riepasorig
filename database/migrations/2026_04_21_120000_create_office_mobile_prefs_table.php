<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateOfficeMobilePrefsTable extends Migration
{
    public function up()
    {
        Schema::create('office_mobile_prefs', function (Blueprint $table) {
            $table->unsignedInteger('office_id')->primary();
            $table->unsignedTinyInteger('lift_slot_count')->default(10);
            $table->timestamp('updated_at')->nullable();
        });

        DB::table('office_mobile_prefs')->insert([
            ['office_id' => 1, 'lift_slot_count' => 10, 'updated_at' => now()],
            ['office_id' => 2, 'lift_slot_count' => 10, 'updated_at' => now()],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('office_mobile_prefs');
    }
}
