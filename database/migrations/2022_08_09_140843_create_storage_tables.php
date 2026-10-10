<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStorageTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_storage', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');

            $table->integer('user_id')->unsigned()->index();
            $table->integer('count')->unsigned();

            $table->integer('storable_id')->unsigned();
            $table->string('storable_type')->default('App/Models/Item/Item');   // Allows multiple types of things to be stored

            $table->integer('storer_id')->unsigned()->index();
            $table->string('storer_type')->default('App/Models/User/UserItem'); // Specifically points to the User[blank]

            $table->text('data')->nullable()->default(null);
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
        Schema::dropIfExists('user_storage');
    }
}
