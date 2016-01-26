<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // create table
        Schema::create('codes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('vendor_id')->unsigned();
            $table->string('name')->unique();
            $table->timestamps();
        });

        // foreign keys
        Schema::table('codes', function($table) {
            $table->foreign('vendor_id')->references('id')->on('vendors');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // drop table
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        Schema::dropIfExists('codes');
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
}
