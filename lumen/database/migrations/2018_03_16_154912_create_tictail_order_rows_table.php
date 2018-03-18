<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTictailOrderRowsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create("tictail_order_rows", function (Blueprint $table)
		{
			$table->increments("row_id")->unsigned(); // Not Tictail id.
			$table->integer("next_row_id")->unsigned()->nullable(); // Id for the next order_row for order, 0 for no .
// id			"string"	ID of the item.

			$table->integer("order_id")->unsigned()->nullable(); // Not Tictail order_id.
// order_id		"string"	The order ID this item belongs to.

// image_url	"string"	URL to an image of the product.

// currency		"string"	Currency in 'ISO 4217 format' for prices in this object.
			$table->enum("currency",["SEK"]);

// price		"number"	Unit price for this product variation.
			$table->integer("price");

// quantity		"number"	Quantity purchased.
			$table->integer("quantity");

// total		"number"	Total price for this item (``price * quantity´´).
			$table->integer("price_total");

			$table->integer("sales_product_id")->unsigned(); // Reference to product id in Sales module. Mapping stored by "tictail_products" table.
// product_id	"string"	ID of the product this item was created from.

// product_slug	"string"	URL slug for the product.

// variation_id	"string"	ID of the variation this item was created from.

// title		"string"	The product title.

// subtitle		"string"	The variation title.

// sku			"string"	Stock keeping unit for this product.

			$table->foreign("order_id")->references("order_id")->on("tictail_orders");
			$table->unique("next_row_id");
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop("tictail_order_rows");
	}
}
