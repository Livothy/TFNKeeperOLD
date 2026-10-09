<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCharacterLikes extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        Schema::create('character_likes', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('character_id');
            $table->unsignedInteger('user_id');
            $table->timestamp('liked_at')->nullable()->default(null);
        });

        Schema::table('character_profiles', function (Blueprint $table) {
            $table->unsignedInteger('like_count')->default(0);
        });

        Schema::table('user_settings', function (Blueprint $table) {
            $table->boolean('allow_character_likes')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        Schema::dropIfExists('character_likes');
        Schema::table('character_profiles', function (Blueprint $table) {
            $table->dropColumn('like_count');
        });
        Schema::table('user_settings', function (Blueprint $table) {
            $table->dropColumn('allow_character_likes');
        });
    }
}
