<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeLitreAmountFieldsToDecimal extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // atg_readings table - 1 field
        Schema::table('atg_readings', function (Blueprint $table) {
            $table->decimal('litre_readings', 15, 4)->default(0)->change();
        });
        // atg_transactions table - 2 fields
        Schema::table('atg_transactions', function (Blueprint $table) {
            $table->decimal('cm', 15, 4)->default(0)->change();
            $table->decimal('liters', 15, 4)->default(0)->change();
        });
        // customer_transactions table - 3 fields
        Schema::table('customer_transactions', function (Blueprint $table) {
            $table->decimal('cost_exc_vat', 15, 4)->default(0)->change();
            $table->decimal('vat', 15, 4)->default(0)->change();
            $table->decimal('total_cost', 15, 4)->default(0)->change();
        });
        // fuel_adjustments table - 1 field
        Schema::table('fuel_adjustments', function (Blueprint $table) {
            $table->decimal('litres', 15, 4)->default(0)->change();
        });
        // fuel_drops table - 4 fields
        Schema::table('fuel_drops', function (Blueprint $table) {
            $table->decimal('litres', 15, 4)->default(0)->change();
            $table->decimal('tot_exc_vat', 15, 4)->default(0)->change();
            $table->decimal('tot_inc_vat', 15, 4)->default(0)->change();
            $table->decimal('vat', 15, 4)->default(0)->change();
        });
        // fuel_transfers table - 1 field
        Schema::table('fuel_transfers', function (Blueprint $table) {
            $table->decimal('litres', 15, 4)->default(0)->change();
        });
        // grades table - 4 fields
        Schema::table('grades', function (Blueprint $table) {
            $table->decimal('price', 15, 4)->default(0)->change();
            $table->decimal('cur_rate', 15, 4)->default(0)->change();
            $table->decimal('new_rate', 15, 4)->default(0)->change();
            $table->decimal('vat_rate', 15, 4)->default(0)->change();
        });
        // payments table - 1 field
        Schema::table('payments', function (Blueprint $table) {
            $table->decimal('amount', 15, 4)->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
