<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVateToFuelDropsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('fuel_drops', function (Blueprint $table) {
            $table->dropColumn('per_litre_price');
            $table->string('vat');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('fuel_drops', function (Blueprint $table) {
            $table->dropColumn('vat');
        });
    }
}
