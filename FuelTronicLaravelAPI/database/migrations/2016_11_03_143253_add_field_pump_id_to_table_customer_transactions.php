<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldPumpIdToTableCustomerTransactions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customer_transactions', function (Blueprint $table) {
            $table->integer('pump_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('customer_transactions', function (Blueprint $table) {
	        $table->dropColumn('pump_id');
        });
    }
}
