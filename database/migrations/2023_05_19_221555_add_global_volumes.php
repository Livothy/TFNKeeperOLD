<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGlobalVolumes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //adding global volumes 
        //is even 1 person unlocks these ones, then all users can see what they contain
        //good for global events/plots.... which is why i am making this, because i have something like this and this would streamline it lmao. 
        Schema::table('volumes', function(Blueprint $table) {
            $table->boolean('is_global')->default(false)->nullable(false);
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
