<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('markings', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name', 50)->nullable();
            $table->string('slug', 25);
            $table->integer('marking_image_id')->nullable();
            $table->integer('rarity_id')->nullable();
            $table->integer('species_id')->nullable();
            $table->string('short_description', 250)->nullable();
            $table->text('description')->nullable();
            $table->string('recessive', 10)->nullable();
            $table->string('dominant', 10)->nullable();
            $table->integer('order_in_genome')->nullable();
            $table->boolean('is_visible');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('markings');
    }
};
