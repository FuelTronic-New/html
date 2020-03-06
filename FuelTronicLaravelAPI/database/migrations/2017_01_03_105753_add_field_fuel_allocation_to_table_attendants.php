<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldFuelAllocationToTableAttendants extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('attendants', function (Blueprint $table) {
            $table->integer('fuel_allocation')->default(-1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('attendants', function (Blueprint $table) {
	        $table->dropColumn('fuel_allocation');
        });
    }
}
