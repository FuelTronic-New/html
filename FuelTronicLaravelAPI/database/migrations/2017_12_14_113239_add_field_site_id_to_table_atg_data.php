<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldSiteIdToTableAtgData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('atg_data', function (Blueprint $table) {
            $table->integer('site_id')->after('name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('atg_data', function (Blueprint $table) {
            $table->dropColumn('site_id');
        });
    }
}
