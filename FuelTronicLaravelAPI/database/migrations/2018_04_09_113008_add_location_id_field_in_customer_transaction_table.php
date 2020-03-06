<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLocationIdFieldInCustomerTransactionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customer_transactions', function (Blueprint $table) {
            $table->integer('location_id')->nullable()->after('vehicle_id');
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
            $table->dropColumn('location_id');
        });
    }
}
