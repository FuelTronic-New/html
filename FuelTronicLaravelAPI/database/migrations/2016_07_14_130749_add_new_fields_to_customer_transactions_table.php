<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewFieldsToCustomerTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customer_transactions', function (Blueprint $table) {
            $table->integer('job_id');
            $table->string('pin');
            $table->string('odo_meter');
            $table->string('cost_exc_vat');
            $table->string('vat');
            $table->string('total_cost');
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
            $table->dropColumn('job_id');
            $table->dropColumn('pin');
            $table->dropColumn('odo_meter');
            $table->dropColumn('cost_exc_vat');
            $table->dropColumn('vat');
            $table->dropColumn('total_cost');
        });
    }
}
