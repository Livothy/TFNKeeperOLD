<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        Schema::create('raids', function (Blueprint $table) {
            $table->id();
            $table->string('name');

            $table->text('description')->nullable()->default(null);
            $table->text('parsed_description')->nullable()->default(null);

            $table->text('data')->nullable()->default(null);

            $table->timestamp('start_at')->nullable()->default(null);
            $table->timestamp('end_at')->nullable()->default(null);

            $table->string('background_hash', 10)->nullable();
            $table->string('background_extension', 5)->nullable();
            $table->boolean('has_background')->default(0);

            $table->boolean('is_visible')->default(0);

            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('raid_bosses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('raid_id')->unsigned();

            $table->text('description')->nullable()->default(null);
            $table->text('parsed_description')->nullable()->default(null);
            $table->text('data')->nullable()->default(null);

            $table->integer('health')->unsigned()->nullable()->default(null);
            $table->integer('damage')->unsigned()->default(0);

            $table->boolean('is_visible')->default(0);
            $table->integer('sort')->unsigned()->default(0);

            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('raid_boss_images', function (Blueprint $table) {
            $table->id();

            $table->integer('raid_boss_id')->unsigned();
            $table->integer('health_threshold')->unsigned()->nullable()->default(null);

            $table->string('hash', 10)->nullable();
            $table->string('extension', 5)->nullable();
            $table->boolean('has_image')->default(0);

            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('raid_rewards', function (Blueprint $table) {
            $table->integer('raid_id')->unsigned()->default(0);
            $table->string('rewardable_type');
            $table->integer('rewardable_id')->unsigned();
            $table->integer('quantity')->unsigned();
            $table->integer('damage_required')->unsigned()->nullable()->default(null);
        });

        Schema::create('raids_log', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->unsigned();
            $table->integer('raid_id')->unsigned();

            $table->text('log');
            $table->string('log_type');
            $table->text('data')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        Schema::dropIfExists('raids');
        Schema::dropIfExists('raid_bosses');
        Schema::dropIfExists('raid_boss_images');
        Schema::dropIfExists('raid_rewards');
        Schema::dropIfExists('raids_log');
    }
};
