<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeLitreAmountFieldsToDecimalTanksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */

    public function __construct()
    {
       DB::getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
    }

    public function up()
    {
        // tanks table - 4 fields
        Schema::table('tanks', function (Blueprint $table) {
            $table->decimal('initial_level', 15, 4)->default(0)->change();
            $table->decimal('min_level', 15, 4)->default(0)->change();
            $table->decimal('last_dip_reading', 15, 4)->default(0)->change();
            $table->decimal('cur_level_stock', 15, 4)->default(0)->change();
            $table->decimal('litre', 15, 4)->default(0)->change();
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
