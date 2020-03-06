<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHosesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hoses', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->default('');
            $table->string('optional1')->default('');
            $table->integer('pump_id')->unsigned()->default(0);
            $table->foreign('pump_id')->references('id')->on('pumps')->onDelete('cascade');
            $table->integer('tank_id')->unsigned()->default(0);
            $table->foreign('tank_id')->references('id')->on('tanks')->onDelete('cascade');
            $table->integer('site_id')->unsigned()->default(0);
            $table->foreign('site_id')->references('id')->on('sites')->onDelete('cascade');
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
        Schema::drop('hoses');
    }
}
