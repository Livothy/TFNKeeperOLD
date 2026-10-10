<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSiteForms extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('site_forms', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->string('title');
            $table->text('description')->nullable()->default(null);
            $table->text('parsed_description')->nullable()->default(null);
            $table->timestamps();
            $table->timestamp('start_at')->nullable()->default(null);
            $table->timestamp('end_at')->nullable()->default(null);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_timed')->default(false);
            $table->boolean('is_anonymous')->default(true);
        });

        Schema::create('site_form_questions', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('form_id')->unsigned()->index();
            $table->text('question');
            $table->boolean('has_options')->default(true); // if no options we render a text field as response, otherwise we know to look up the options
        });

        Schema::create('site_form_options', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('question_id')->unsigned()->index();
            $table->text('option');
        });

        Schema::create('site_form_answers', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('form_id')->unsigned()->index();
            $table->integer('question_id')->unsigned()->index();
            $table->integer('option_id')->unsigned()->nullable()->default(null)->index();
            $table->integer('user_id')->unsigned()->nullable()->default(null);
            $table->text('answer')->nullable()->default(null);
            $table->timestamps();
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
        Schema::dropIfExists('forms');
        Schema::dropIfExists('form_question');
        Schema::dropIfExists('form_option');
        Schema::dropIfExists('form_answer');

    }
}
