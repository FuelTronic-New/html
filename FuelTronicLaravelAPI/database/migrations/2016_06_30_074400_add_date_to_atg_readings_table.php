<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDateToAtgReadingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('atg_readings', function (Blueprint $table) {
            $table->dateTime('reading_time');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('atg_readings', function (Blueprint $table) {
            $table->dropColumn('reading_time');
        });
    }
}
