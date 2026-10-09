<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('marking_carriers', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->integer('marking_id');
            $table->integer('carrier_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('marking_carriers');
    }
};
