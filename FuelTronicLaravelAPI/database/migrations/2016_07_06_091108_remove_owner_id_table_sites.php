<?php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveOwnerIdTableSites extends Migration
{
	/**
	 * Run the migrations.
	 * @return void
	 */
	public function up()
	{
		Schema::table('sites', function (Blueprint $table) {
			$table->dropForeign(['owner_id']);
			$table->dropColumn('owner_id');
		});
	}

	/**
	 * Reverse the migrations.
	 * @return void
	 */
	public function down()
	{
		Schema::table('sites', function (Blueprint $table) {
			//
		});
	}
}
