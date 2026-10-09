<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('character_markings', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('character_id');
            $table->integer('marking_id');
            $table->string('code', 10);
            $table->integer('order');
            $table->boolean('is_dominant');
            $table->string('data', 191)->nullable();
            $table->string('base_id', 11)->nullable();
            $table->integer('carrier_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('character_markings', function (Blueprint $table) {
            Schema::dropIfExists('character_markings');
        });
    }
};
