<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePumpsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pumps', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->default('');
            $table->string('ip')->default('');
            $table->string('code')->default('');
            $table->string('optional1')->default('');
            $table->string('optional2')->default('');
            $table->string('optional3')->default('');
            $table->text('attendent_tag');
            $table->text('vehicle_tag');
            $table->text('odo_meter');
            $table->text('pin');
            $table->text('job_tag');
            $table->text('group_tag_1');
            $table->text('group_tag_2');
            $table->text('group_tag_3');
            $table->text('group_tag_4');
            $table->text('group_tag_5');
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
        Schema::drop('pumps');
    }
}
