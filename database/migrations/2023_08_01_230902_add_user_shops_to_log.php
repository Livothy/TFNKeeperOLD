<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddUserShopsToLog extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        DB::statement("ALTER TABLE items_log MODIFY COLUMN sender_type ENUM('User', 'Character', 'Shop') DEFAULT NULL");
        DB::statement("ALTER TABLE items_log MODIFY COLUMN recipient_type ENUM('User', 'Character', 'Shop') DEFAULT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        DB::statement("ALTER TABLE items_log MODIFY COLUMN sender_type ENUM('User', 'Character') DEFAULT NULL");
        DB::statement("ALTER TABLE items_log MODIFY COLUMN recipient_type ENUM('User', 'Character') DEFAULT NULL");
    }
}
