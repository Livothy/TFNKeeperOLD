<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBookExtras extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('books', function(Blueprint $table) {
            $table->boolean('is_public')->default(false)->nullable(false);
            $table->boolean('has_next')->default(false)->nullable(false);
            $table->integer('bookshelf_id')->unsigned()->nullable()->default(null);
            $table->integer('sort')->unsigned()->default(0);
        });

        Schema::create('bookshelves', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name', 191)->nullable(false);
            $table->boolean('has_image')->default(false)->nullable(false);
            $table->string('summary', 256)->nullable()->default(null);
            $table->integer('sort')->unsigned()->default(0);
        });

        Schema::table('volumes', function(Blueprint $table) {
            $table->integer('sort')->unsigned()->default(0);
        });

        Schema::create('book_authors', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('book_id')->unsigned();
            $table->string('author_type')->nullable();
            $table->string('credit_type')->nullable()->default(null);
            $table->string('author')->nullable()->default(null);
        });

        Schema::create('book_tags', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('book_id')->unsigned()->index();
            $table->string('tag')->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bookshelves');
        Schema::dropIfExists('book_authors');
        Schema::dropIfExists('book_tags');
        Schema::table('books', function(Blueprint $table) {
            $table->dropColumn('is_public');
            $table->dropColumn('has_next');
            $table->dropColumn('bookshelf_id');
            $table->dropColumn('sort');
        });
        Schema::table('volumes', function(Blueprint $table) {
            $table->dropColumn('sort');
        });
    }
}
