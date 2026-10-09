<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->string('status_message')->nullable()->default(null);
            $table->timestamp('clear_status_on')->nullable()->default(null);
            $table->timestamp('status_set_on')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->dropColumn('status_message');
            $table->dropColumn('clear_status_on');
            $table->dropColumn('status_set_on');
        });
    }
};
