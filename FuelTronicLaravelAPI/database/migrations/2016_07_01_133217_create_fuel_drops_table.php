<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFuelDropsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fuel_drops', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('site_id');
            $table->integer('tank_id');
            $table->integer('grade_id');
            $table->integer('supplier_id');
            $table->integer('litres');
            $table->dateTime('purchase_date');
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
        Schema::drop('fuel_drops');
    }
}
