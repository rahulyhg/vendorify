<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {   
        // create table
        Schema::create('transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('vendor_id')->unsigned();
            $table->integer('payment_id')->unsigned();
            
            $table->string('code');
            $table->integer('quantity');
            $table->string('description');
            $table->decimal('gross', 6, 2);
            $table->decimal('discounts', 6, 2);
            $table->decimal('net', 6, 2);
            $table->boolean('custom')->default(0);
            $table->boolean('refund')->default(0);

            $table->date('processed_at');
            $table->timestamps();
        });

        // foreign keys/indexes
        Schema::table('transactions', function($table) {
            $table->index('vendor_id');
            $table->foreign('payment_id')->references('id')->on('payments');
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
        Schema::dropIfExists('transactions');
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
}
