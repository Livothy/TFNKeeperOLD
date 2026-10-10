<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRoleRewards extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        Schema::create('rank_rewards', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->unsignedInteger('rank_id')->index();
            $table->text('data')->nullable()->default(null);
            $table->boolean('is_active')->default(0);
            // 1 = daily, 2 = weekly (Mondays), 3 = monthly (the 1st)
            $table->unsignedInteger('reward_time')->default(2);
            // Guards against paying out twice on the same day if the command runs more than once
            $table->timestamp('last_rewarded_at')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        Schema::dropIfExists('rank_rewards');
    }
}
