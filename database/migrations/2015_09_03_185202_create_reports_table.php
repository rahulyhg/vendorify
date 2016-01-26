<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        // create table
        Schema::create('reports', function (Blueprint $table) {
            $table->increments('id');
            $table->date('start_date');
            $table->date('end_date');
            $table->longText('data');
            $table->text('message');
            $table->boolean('include_rent');

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
        // drop table
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        Schema::dropIfExists('reports');
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
}
