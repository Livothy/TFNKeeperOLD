<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        Schema::create('user_pages', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->unsigned();

            $table->string('title');
            $table->string('key');

            $table->text('text')->nullable()->default(null);
            $table->text('parsed_text')->nullable()->default(null);

            $table->boolean('is_visible')->default(1);
            $table->boolean('show_on_profile')->default(1);
            $table->boolean('can_comment')->default(0);

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        Schema::dropIfExists('user_pages');
    }
};
