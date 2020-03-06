<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsToGradesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('grades', function (Blueprint $table) {
            $table->string('cur_rate');
            $table->dateTime('rate_increased_at');
            $table->string('new_rate');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('grades', function (Blueprint $table) {
            $table->dropColumn('cur_rate');
            $table->dropColumn('rate_increased_at');
            $table->dropColumn('new_rate');
        });
    }
}
