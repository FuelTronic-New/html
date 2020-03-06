<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsToTanksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tanks', function (Blueprint $table) {
            $table->enum('atg', ['On', 'Off']);
            $table->enum('manual_reading', ['On', 'Off']);
            $table->enum('status', ['Active', 'Inactive']);
            $table->string('initial_level');
            $table->string('volume');
            $table->string('min_level');
            $table->string('cur_atg_level');
            $table->string('last_dip_reading');
            $table->string('cur_level_stock');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tanks', function (Blueprint $table) {
            $table->dropColumn('atg');
            $table->dropColumn('manual_reading');
            $table->dropColumn('status');
            $table->dropColumn('initial_level');
            $table->dropColumn('volume');
            $table->dropColumn('min_level');
            $table->dropColumn('cur_atg_level');
            $table->dropColumn('last_dip_reading');
            $table->dropColumn('cur_level_stock');
        });
    }
}
