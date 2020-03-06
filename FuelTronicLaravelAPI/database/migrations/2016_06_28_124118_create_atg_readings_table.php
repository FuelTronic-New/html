<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAtgReadingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('atg_readings', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->default('');
            $table->string('litre_readings')->default('');
            $table->integer('tank_id')->unsigned()->default(0);
            $table->foreign('tank_id')->references('id')->on('tanks')->onDelete('cascade');
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
        Schema::drop('atg_readings');
    }
}
