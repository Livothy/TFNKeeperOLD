<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSiteFormLikesAndAnswerId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('site_forms', function(Blueprint $table) {
            $table->boolean('allow_likes')->default(false);
        });

        Schema::table('site_form_answers', function(Blueprint $table) {
            $table->integer('submission_number')->unsigned()->default(1); // to tie multiple submission answers together
        });

        
        Schema::create('site_form_likes', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('answer_id')->unsigned()->index();
            $table->integer('user_id')->unsigned()->index();
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
            $table->dropcolumn('allow_likes');
        });
        Schema::table('site_form_answers', function(Blueprint $table) {
            $table->dropcolumn('submission_number');
        });
        Schema::dropIfExists('site_form_likes');

    }
}
