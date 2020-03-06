<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewColumnMotivationToTableFuelAdjustment extends Migration
{
    /**
     * Run the migrations.
     *
     *  Add new column motivation because each fuel adjustment must have a motivation
     *
     * @return void
     */
    public function up()
    {
        Schema::table('fuel_adjustments', function (Blueprint $table) {
            $table->string('motivation')->after('site_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('fuel_adjustments', function (Blueprint $table) {
            $table->dropColumn('motivation');
        });
    }
}
