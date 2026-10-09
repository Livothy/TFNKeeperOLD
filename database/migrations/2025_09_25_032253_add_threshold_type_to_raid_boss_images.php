<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        Schema::table('raid_boss_images', function (Blueprint $table) {
            $table->string('threshold_type')->default('percent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        Schema::table('raid_boss_images', function (Blueprint $table) {
            $table->dropColumn('threshold_type');
        });
    }
};
