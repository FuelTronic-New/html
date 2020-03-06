<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->default('');
            $table->string('transaction_start')->default('');
            $table->string('transaction_end')->default('');
            $table->string('amount')->default('');
            $table->string('odo_meter');
            $table->text('pin')->nullable();
            $table->integer('job_id')->nullable();
            $table->integer('group_id_1')->nullable();
            $table->integer('group_id_2')->nullable();
            $table->integer('group_id_3')->nullable();
            $table->integer('group_id_4')->nullable();
            $table->integer('group_id_5')->nullable();
            $table->integer('hose_id')->unsigned()->default(0);
            $table->foreign('hose_id')->references('id')->on('hoses')->onDelete('cascade');
            $table->integer('attendant_id')->unsigned()->default(0);
            $table->foreign('attendant_id')->references('id')->on('attendants')->onDelete('cascade');
            $table->integer('vehicle_id')->unsigned()->default(0);
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('cascade');
            $table->integer('site_id')->unsigned()->default(0);
            $table->foreign('site_id')->references('id')->on('sites')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('transactions');
    }
}
