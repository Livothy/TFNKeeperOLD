<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        Schema::table('raids', function (Blueprint $table) {
            $table->integer('status')->unsigned()->default(0);
            $table->timestamp('distributed_at')->nullable()->default(null);
            $table->boolean('continue_raid')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        Schema::table('raids', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->dropColumn('distributed_at');
            $table->dropColumn('continue_raid');
        });
    }
};
