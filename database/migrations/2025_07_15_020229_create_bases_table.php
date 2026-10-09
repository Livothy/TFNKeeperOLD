<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('bases', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name', 50)->nullable();
            $table->integer('image_id')->nullable();
            $table->string('code', 10)->nullable();
            $table->boolean('is_visible');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('bases', function (Blueprint $table) {
            Schema::dropIfExists('bases');
        });
    }
};
