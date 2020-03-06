<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFuelAdjustmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fuel_adjustments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('tank_id');
            $table->integer('litres');
            $table->string('mode', 2);
            $table->integer('created_by');
            $table->integer('site_id');
            $table->softDeletes();
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
        Schema::drop('fuel_adjustments');
    }
}
