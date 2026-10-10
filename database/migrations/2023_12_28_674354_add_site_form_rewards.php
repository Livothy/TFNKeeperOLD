<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSiteFormRewards extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('site_form_rewards', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('form_id')->unsigned()->default(0);
            $table->string('rewardable_type');
            $table->integer('rewardable_id')->unsigned();
            $table->integer('quantity')->unsigned();
            $table->foreign('form_id')->references('id')->on('site_forms');
        });

        Schema::table('site_form_questions', function(Blueprint $table) {
            $table->boolean('is_mandatory')->default(false);
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
        Schema::dropIfExists('site_form_rewards');

        Schema::table('site_form_questions', function(Blueprint $table) {
            $table->dropcolumn('is_mandatory');
        });
    }
}
