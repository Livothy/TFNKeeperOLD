<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMultipleFetchQuests extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fetch_quests', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            //optional name for who gives the quest
            //nullable, not really important
            $table->string('questgiver_name')->nullable()->default(null);
            $table->string('name');
            $table->text('description')->nullable()->default(null);
            $table->text('parsed_description')->nullable()->default(null);

            $table->boolean('is_active')->default(1);
            $table->boolean('has_image')->default(0);
            $table->integer('cooldown')->nullable()->default(null);

            //set to null when we first make the fetch
            $table->integer('fetch_item')->unsigned()->nullable()->default(null);
            $table->integer('fetch_category')->unsigned()->nullable()->default(null);

            //rewards
            $table->integer('currency_id')->unsigned()->nullable()->default(null);
            $table->integer('current_min')->unsigned()->nullable()->default(null);
            $table->integer('current_max')->unsigned()->nullable()->default(null);
            //data table for all the currencies oh my god this is already a nightmare what have i done
            $table->text('extras')->nullable()->default(null);
        });

        Schema::create('fetch_log', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('fetch_id')->unsigned()->index();
            $table->integer('user_id')->unsigned()->index();

            $table->integer('item_id')->unsigned();
            $table->timestamps();
        });

        Schema::create('fetch_exceptions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('fetch_quest_id');
            $table->string('exception_type');
            $table->integer('exception_id');
        });

        Schema::create('fetch_rewards', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('fetch_quest_id');
            $table->string('rewardable_type');
            $table->integer('rewardable_id');
            $table->integer('quantity')->unsigned();
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
    }
}
