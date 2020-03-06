<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldAtgIdToTableAtgTransactions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('atg_transactions', function (Blueprint $table) {
            $table->integer('atg_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('atg_transactions', function (Blueprint $table) {
	        $table->dropColumn('atg_id');
        });
    }
}
