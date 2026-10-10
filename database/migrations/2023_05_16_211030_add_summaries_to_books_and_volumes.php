<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSummariesToBooksAndVolumes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        //deciding to add summaries to them to have a quick glance of what the book ro volume contains 
        //users can click to view the full book/volume so admins can put much longer info without swamping the index pages for the books and volumes
        Schema::table('volumes', function(Blueprint $table) {
            $table->string('summary', 256)->nullable()->default(null);
        });

        Schema::table('books', function(Blueprint $table) {
            $table->string('summary', 256)->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}
