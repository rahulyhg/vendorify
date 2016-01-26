<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVendorsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vendors', function (Blueprint $table) {
            $table->increments('id');

            $table->string('name')->default('');
            $table->string('business')->default('');
            $table->string('phone')->default('');
            $table->enum('status', ['pending','inactive','active','flagged'])->default('pending');

            $table->integer('rent')->default(0);
            $table->integer('rate')->default(20);
            $table->boolean('email_notification')->default(0);
            $table->boolean('sms_notification')->default(0);

            $table->string('email')->unique();
            $table->string('password', 60);
            $table->string('group', 60);
            $table->string('google_token')->default('');
            $table->string('google_email')->default('');
            $table->text('notes')->default('');
            $table->rememberToken();
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
        Schema::dropIfExists('vendors');
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
}
