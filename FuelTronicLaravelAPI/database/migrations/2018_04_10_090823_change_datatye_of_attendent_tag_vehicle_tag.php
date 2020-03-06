<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeDatatyeOfAttendentTagVehicleTag extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pumps', function (Blueprint $table) {
            $table->integer('vehicle_tag')->change();
            $table->integer('attendent_tag')->change();
            $table->integer('job_tag')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pumps', function (Blueprint $table) {
            $table->string('vehicle_tag')->change();
            $table->string('attendent_tag')->change();
            $table->string('job_tag')->change();
        });
    }
}
