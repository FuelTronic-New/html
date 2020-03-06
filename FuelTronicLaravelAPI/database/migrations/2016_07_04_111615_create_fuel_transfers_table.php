<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFuelTransfersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fuel_transfers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('from_site');
            $table->integer('from_tank');
            $table->integer('to_site');
            $table->integer('to_tank');
            $table->integer('litres');
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
        Schema::drop('fuel_transfers');
    }
}
