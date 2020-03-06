<?php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddActiveInactiveToVehiclesTable extends Migration
{
	/**
	 * Run the migrations.
	 * @return void
	 */
	public function up()
	{
		Schema::table('vehicles', function (Blueprint $table) {
			$table->string('status')->default('');
		});
	}

	/**
	 * Reverse the migrations.
	 * @return void
	 */
	public function down()
	{
		Schema::table('vehicles', function (Blueprint $table) {
			$table->dropColumn('status');
		});
	}
}
