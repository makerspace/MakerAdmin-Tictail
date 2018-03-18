<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTictailOrdersTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create("tictail_orders", function (Blueprint $table)
		{
			$table->increments("order_id")->unsigned();
// id"						"string"	"The ID of this order.
			$table->integer("tictail_order_number"); // Should contain string of id and number?
// number"					"number"	"Unique integer for the order (always increasing for newer orders).
// token"					"string"	"Token ID for this order. (Doesn’t require access token)
			$table->integer("store_id")->unsigned(); // Referencing database table stores, not tictail "store_id"
// store_id"				"string"	"ID of the store the order was placed in.
// store"					"store"		"Basic data about store when order was placed.
// cart_id"					"string"	"ID of the cart from which this order was created.
// user_id"					"string"	"ID of the user that placed this order (not necessarily a user with an account at Tictail).
// user_signed_in" 			"boolean" 	"Whether the user was signed in to a Tictail account when the order was placed.
// email"					"string"	"Email of the customer placing this order (as entered in checkout).

			$table->integer("member_id")->nullable(); // The member this order has been connected to.
			$table->text("member_info"); // Info(json) useful to connect member. Discard after connected to member_id?

// status"					"enum"		"Overall status for this order.
//	* open					Order is open. (The order has not been actively cancelled.)
//	* cancelled				Order has been cancelled. (Not necessarily refunded).

// payment_status" 			"enum" 		"Status for the payment of this order. (inspect ``transactions´´)
//	* unpaid				Order not yet paid (a completed order will never have this status)
//	* pending				Payment is pending, could be  ``denied´´. (Gateway should confirm before order is shipped)
//	* paid					The order is fully paid.
//	* refunded_partially	Part of the payment has been refunded (see ``transactions´´ to see amounts).
//	* refunded_fully		The payment has been fully refunded. (Refunded up to full amount).

// dispute_status" 	"enum" 		"Dispute status for this order. For more details, inspect ``disputes´´.
			$table->enum("dispute_status", [
				"undisputed", 			//	Order not disputed.
				"eligible",				//	Order is eligible for dispute.
				"ineligible",			//	Order is not eligible for dispute.
				"reported",				//	Order reported by customer, but not a dispute.
				"awaiting_evidence",	//	Order escalated to dispute and awaiting evidence from merchant.
				"awaiting_resolution",	//	Dispute awaiting resolution, From Tictail or from external gateway.
				"resolved"				//	Dispute resolved, either way.
			]);

// fulfillment_status" 	"enum" 	"Status for shipment of this order. For more details, inspect ``fulfillment´´.
//	* unfulfilled			Order has not yet been shipped.
//	* fulfilled				Order has been shipped.

			$table->enum("status", [// Not tictail "status"
				"awaiting_payment",
				"awaiting_dispute",
				"resolve_member_id",
				"unhandled",
				"finished",
				"cancelled_awaiting_refund",
				"cancelled_finished"
			]);

// display_status" 			"enum" 		"Status combining other statuses suitable for display in interfaces.
//	* cancelled				Order has been cancelled.
//	* pending				Order’s payment is pending.
//	* awaiting_fulfillment	Order is waiting to be shipped.
//	* awaiting_pick_up		Order is waiting to be picked up.
//	* refunded_partially	Order has been partially refunded.
//	* refunded_fully		Order has been fully refunded.
//	* shipped				Order has been shipped.
//	* picked_up				Order has been picked up.
//	* denied				Order’s payment was previously ``pending´´ but has now settled as ``denied´´.
//	* info_received			Order has been registered with the carrier that will ship it.
//	* in_transit			Order is in transit to its destination.
//	* out_for_delivery		Order is waiting to be picked up at the destination.
//	* failed_attempt		A delivery attempt for this order has failed.
//	* delivered				Order has been delivered successfully to its destination.
//	* exception				An exception occurred during delivery.

// currency" 				"string" 	"Currency in 'ISO 4217 format' the customer was charged in.
			$table->enum("currency",["SEK"]);
			$table->integer("order_total");
// shipping_address" 		"address" 	"Object describing the address to which this order should be shipped.
			// ADDED TO FIELD 'member_info'

// shipping_line" 			"shipping" 	"Object describing the shipping choices at checkout. (Always exists)
// fulfillment" 			"fulfillment" 	"Object describing the shipment. (Only exists when shipped)
// messages" 				"array" 	"Messages belonging to order. (Currently only shippment message, if specified)
			$table->integer("order_row_id")->unsigned()->nullable();
			// ID INTO TABLE 'tictail_order_rows'?
// items" 					"array" 	"Items belonging order. Each corresponds to purchased product.
			// ADDED TO TABLE 'tictal_order_rows'

// adjustments" 			"array" 	"Adjustments made. Contain at least ``shipping-tax´´ and ``item-tax´´ (even if 0).
			$table->integer("transaction_id")->unsigned()->nullable(); // References last stored transaction for order.
			// ID INTO TABLE 'tictail_transactions'?
// transactions" 			"array" 	"Transactions. Purchase first then any refund transactions.
			// ADDED TO TABLE 'tictail_transactions'

// disputes" 				"array" 	"List of disputes for this order.
// internal_notes" 			"array" 	"Internal order notes. Only visible to merchant. (Not ``user.order.read´´)
// attribution" 			"string" 	"Specifies from where this order came, on the form ``source:platform´´.
// locale" 					"string" 	"The locale used in checkout. All communication will use this locale.
// note" 					"string" 	"Message written by the customer for this order.
			// ADDED TO FIELD 'member_info'

// prices_include_tax" 		"boolean" 	"Whether the prices listed in this order includes tax or not.
// tax_type" 				"string" 	"Type of tax the country where the store is in uses.
// tictail_shopper" 		"boolean" 	"Denotes whether this sale was driven by Tictail.
// discount_total" 			"number" 	"Total amount of discounts. Details in ``adjustments´´.
// grand_total" 			"number" 	"Total amount customer paid at checkout. (Doen't change with refunds)
			// ADDED TO FIELD 'order_total'

// subtotal" 				"number" 	"The item total minus any discounts applied to the order.
// invoice_fee" 			"number" 	"Invoice fee paid for this order. Only applicable for some payment methods.
// invoice_fee_tax" 		"number" 	"Tax on the invoice fee.
// item_total" 				"number" 	"Total amount of the items (products) on this order.
// item_tax" 				"number" 	"The tax amount on the items of this order.
// refundable_total"		"number" 	"Amount available for refund. This decreases with refunds made on the order.
// shipping_total" 			"number" 	"Amount paid for shipping this order.
// shipping_tax" 			"number" 	"Tax amount paid on shipping for this order.
// tax_total" 				"number" 	"The total amount of tax on this order.
// created_at" 				"string" 	"Datetime('ISO 8601 format') order placed.
			$table->dateTimeTz("created_at");

// modified_at" 			"string" 	"Datetime('ISO 8601 format') order last modified.
			$table->dateTimeTz("modified_at")->nullable();
			
			// The following rows are set in update file after transactions/order_rows tables has been created
			// $table->foreign("order_row_id")->references("row_id")->on("tictail_order_rows");
			// $table->foreign("transaction_id")->references("transaction_id")->on("tictail_transactions");
			$table->index("member_id");
			$table->unique("tictail_order_number");
			$table->index("status");
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop("tictail_orders");
	}
}
