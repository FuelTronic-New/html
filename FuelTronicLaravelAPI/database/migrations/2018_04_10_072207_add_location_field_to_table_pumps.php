<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLocationFieldToTablePumps extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pumps', function (Blueprint $table) {
            $table->integer('location')->nullable()->default(0)->after('optional3')->comment('1:On 0:Off');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pumps', function (Blueprint $table) {
            $table->dropColumn('location');
        });
    }
}
