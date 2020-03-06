<?php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLitersToTableTanks extends Migration
{
	/**
	 * Run the migrations.
	 * @return void
	 */
	public function up()
	{
		Schema::table('tanks', function (Blueprint $table) {
			$table->integer('litre')->default(0);
		});
	}

	/**
	 * Reverse the migrations.
	 * @return void
	 */
	public function down()
	{
		Schema::table('tanks', function (Blueprint $table) {
			$table->dropColumn('litre');
		});
	}
}
