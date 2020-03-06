<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAtgTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('atg_transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('guid', 50);
            $table->date('date');
            $table->time('time');
            $table->float('cm', 10);
            $table->float('liters', 10);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('atg_transactions');
    }
}
