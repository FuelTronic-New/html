<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAtgDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('atg_data', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('ip_address');
            $table->tinyInteger('port_num');
            $table->integer('tank_type');
            $table->string('sensor_height');
            $table->string('fill_height');
            $table->string('riser_height');
            $table->string('tank_height');
            $table->string('cylinder_length');
            $table->string('endcap_length');
            $table->string('tank_diameter');
            $table->string('height');
            $table->string('guid');
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
        Schema::drop('atg_data');
    }
}
