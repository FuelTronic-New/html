<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSuppliersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('accountNumber');
            $table->string('usage');
            $table->string('status');
            $table->string('name');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email_address');
            $table->string('phone');
            $table->string('fax');
            $table->string('mobile');
            $table->integer('site_id')->unsigned();
            $table->foreign('site_id')->references('id')->on('sites');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('suppliers');
    }
}
