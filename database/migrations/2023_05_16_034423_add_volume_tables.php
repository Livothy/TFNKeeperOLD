<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVolumeTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //books are what hold the volumes
        //think of it like a category
        Schema::create('books', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name', 191)->nullable(false);
            $table->boolean('has_image')->default(false)->nullable(false);
            $table->text('description')->nullable();
            $table->text('parsed_description')->nullable();
            $table->boolean('is_visible')->default(1);
        });

        //volumes are what can be collected by the users
        //volumes have fun data like lore and whatnot
        //we will make its own model and table because it's easier to access and edit 
        //also i am lazy
        Schema::create('volumes', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name', 191)->nullable(false);
            $table->boolean('has_image')->default(false)->nullable(false);
            $table->text('description')->nullable();
            $table->text('parsed_description')->nullable();
            $table->integer('book_id')->unsigned()->nullable()->default(null);
            $table->boolean('is_visible')->default(1);
        });

        //we will be making a table to keep track of what volumes a user owns
        Schema::create('user_volumes', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('volume_id'); //
            $table->timestamps();
        });

        //log? not sure if this is needed due to the nature of the ext but we will keep it just in case to prevent having to make a lot more migrations if things change along the way
        Schema::create('user_volumes_log', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->unsignedInteger('volume_id'); 
            $table->string('log', 500);
            $table->string('log_type');
            $table->string('data', 1024)->nullable();
            $table->unsignedInteger('sender_id')->nullable(); 
            $table->unsignedInteger('recipient_id')->nullable();
            $table->unsignedInteger('character_id')->nullable(); 

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
    }
}
