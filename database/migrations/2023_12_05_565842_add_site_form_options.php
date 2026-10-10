<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSiteFormOptions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('site_forms', function(Blueprint $table) {
            $table->boolean('is_public')->default(true);
            $table->boolean('is_editable')->default(true);
            $table->string('timeframe')->default('lifetime');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table('site_forms', function(Blueprint $table) {
            $table->dropcolumn('is_public');
            $table->dropcolumn('is_editable');
            $table->dropcolumn('timeframe');
        });

    }
}
