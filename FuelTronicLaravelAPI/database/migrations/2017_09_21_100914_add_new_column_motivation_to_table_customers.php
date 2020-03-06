<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewColumnMotivationToTableCustomers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->text('address_line_1')->after('mobile');
            $table->text('address_line_2')->after('address_line_1');
            $table->text('address_line_3')->after('address_line_2');
            $table->text('address_line_4')->after('address_line_3');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('address_line_1');
            $table->dropColumn('address_line_2');
            $table->dropColumn('address_line_3');
            $table->dropColumn('address_line_4');
        });
    }
}
