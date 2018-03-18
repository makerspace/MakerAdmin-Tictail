<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateTictailOrdersTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table("tictail_orders", function (Blueprint $table) {
			$table->foreign("order_row_id")->references("row_id")->on("tictail_order_rows");
			$table->foreign("transaction_id")->references("transaction_id")->on("tictail_transactions");
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table("tictail_orders", function (Blueprint $table) {
			$table->dropForeign("order_row_id");
			$table->dropForeign("transaction_id");
		});
	}
}
