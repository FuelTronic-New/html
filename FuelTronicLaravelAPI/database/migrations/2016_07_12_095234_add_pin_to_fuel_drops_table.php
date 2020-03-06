<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPinToFuelDropsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('fuel_drops', function (Blueprint $table) {
            $table->string('per_litre_price', 30);
            $table->string('tot_exc_vat', 30);
            $table->string('tot_inc_vat', 30);
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
            $table->dropColumn('per_litre_price');
            $table->dropColumn('tot_exc_vat');
            $table->dropColumn('tot_inc_vat');
        });
    }
}
