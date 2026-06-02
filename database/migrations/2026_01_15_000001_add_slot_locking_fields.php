<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSlotLockingFields extends Migration
{
    public function up()
    {
        Schema::table('slots', function (Blueprint $table) {
            $table->integer('version')->default(0)->after('slot_id');
            $table->dateTime('reserved_until')->nullable()->after('version');
            $table->string('reserved_by', 100)->nullable()->after('reserved_until');
            $table->tinyInteger('extension_count')->default(0)->after('reserved_by');
            
            $table->index(['date', 'queue_id', 'iorder']);
            $table->index('reserved_until');
        });
    }

    public function down()
    {
        Schema::table('slots', function (Blueprint $table) {
            $table->dropColumn(['version', 'reserved_until', 'reserved_by', 'extension_count']);
        });
    }
}
