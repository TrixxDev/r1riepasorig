<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('workingdays', function (Blueprint $table) {
            $table->tinyInteger('is_visible')->default(1)->after('is_opened');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workingdays', function (Blueprint $table) {
            $table->dropColumn('is_visible');
        });
    }
};
