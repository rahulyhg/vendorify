<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // create table
        Schema::create('payments', function (Blueprint $table) {
            $table->increments('id');
            $table->string('square_id');
            $table->decimal('total', 6, 2);
            $table->decimal('processing_fee', 6, 2);
            $table->decimal('refunded', 6, 2);
            $table->string('square_url');
            $table->string('tender');
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
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        Schema::dropIfExists('payments');
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
}
