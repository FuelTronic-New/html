<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAttendantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendants', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->default('');
            $table->string('surname')->default('');
            $table->string('cell')->default('');
            $table->string('said')->default('');
            $table->string('tag_id');
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
        Schema::drop('attendants');
    }
}
