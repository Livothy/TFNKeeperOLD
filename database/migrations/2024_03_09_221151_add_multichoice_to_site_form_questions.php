<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMultichoiceToSiteFormQuestions extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('site_form_questions', function (Blueprint $table) {
            $table->boolean('is_multichoice');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('site_form_questions', function (Blueprint $table) {
            $table->dropColumn('is_multichoice');
        });
    }
}
