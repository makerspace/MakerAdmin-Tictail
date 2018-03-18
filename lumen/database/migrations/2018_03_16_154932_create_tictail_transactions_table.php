<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTictailTransactionsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create("tictail_transactions", function (Blueprint $table)
		{
			$table->increments("transaction_id")->unsigned(); // Not Tictail id.
			$table->integer("previous_transaction_id")->unsigned()->nullable();
// id						"string"	ID of the transaction.
			$table->string("tictail_id");

			$table->integer("order_id")->unsigned()->nullable(); // Not Tictail order_id.
// order_id				"string"	The order ID this adjustment belongs to.

// 			token					"string"	Unguessable generated Tictail transaction ID usually sent to gateways as metadata.

// currency					"string"	Currency in 'ISO 4217 format' for prices in this object.
			$table->enum("currency",["SEK"]);

// amount					"number"	Transaction amount.
			$table->integer("amount");

// gateway					"enum"		The payment gateway this transaction was processed through.
// 				"stripe",	// The transaction was processed through Stripe.
// 				"paypal",	// The transaction was processed through PayPal.
// 				"klarna"	// The transaction was processed through Klarna.

// gateway_transaction_id	"string"	The ID of the transaction over at the gateway.
			$table->string("gateway_transaction_id");

			$table->enum("payment_method",[ // Combination of gateway and payment_method
				"unknown",				// Unrecognized payment_method
				"stripe",				// The transaction was processed through Stripe.
				"paypal_credit_card",	// The transaction was processed through PayPal using credit card.
				"paypal_account",		// The transaction was processed through PayPal account.
				"klarna_part_payments",	// The transaction was processed through Klarna part payments.
				"klarna_invoice"		// The transaction was processed through Klarna invoice.
			]);

// payment_method			"enum"		The payment method used at the gateway.
// 			* credit_card						Paid using a credit card (either through Stripe or PayPal).
// 			* stripe							(deprecated) Paid using a credit card through Stripe. Will be replaced with ``credit_card´´.
// 			* invoice							Paid using invoice (only for Klarna).
// 			* part_payments						Paid using part payments/installments (only for Klarna).
// 			* paypal							Paid using PayPal account.

			$table->boolean("is_refund");
// type						"enum"		Type of transaction.
// 			* purhase							Only ever exists one on the order and it is always the first transaction, describing the funds moving from customer to merchant.
// 			* refund							Can exist several on the same order, and describes refunds performed by the merchant to the customer.

// status					"enum"		Status of the transaction.
			$table->enum("status",[
				"pending",		//	The trasaction is currently pending. Depending on the payment service provider it can end up either as ``successful´´ or ``failed´´ when it settles.
				"succeeded",	//	The transaction is successful and funds have reached its destination.
				"failed",		//	The transaction is failed (can only happen if the transaction has been ``pending´´ before). See ``failure_reason´´ for more information.
				"error"			//	An unknown error occured when processing this transaction. See ``error_reason´´ for more information.
			]);

// error_reason				"string"	Human-readable reason for why this transaction errored.
			// Possibly dumped into separate table transaction_id => error_reason

// failure_reason			"string"	Human-readable reason for why this transaction failed.
			// Possibly dumped into separate table transaction_id => failure_reason

// pending_reason			"string"	Human-readable reason for why this transaction is currently pending.
			// Possibly dumped into separate table transaction_id => pending_reason

// refund_reason			"enum"		The reason this refund transaction was created.
// 			* dispute							Because of a dispute.
// 			* item_out_of_stock					Merchant can’t fulfill order because it is out of stock.
// 			* item_not_as_described				Customer says the product isn’t as described, and merchant agrees to a refund.
// 			* item_damage_during_delivery		The item was damaged during delivery.
// 			* item_not_delivered_or_delayed		Item not delivered or delayed delivery.
// 			* shipping_cost						Shipping price was overcharged.
// 			* agreement							Refund according to agreement reached between customer and merchant.

			$table->integer("verification_id")->unsigned()->nullable(); // Reference to the accounting document for this transaction.

// created_at				"string"	Datetime('ISO 8601 format') when this transaction was created.
			$table->dateTimeTz("created_at");

// succeeded_at				"string"	Datetime('ISO 8601 format') when this transaction transitioned to succeeded. Usually this is close to ``created_at´´, but for pending transactions it can differ.
			$table->dateTimeTz("succeeded_at")->nullable();

			$table->foreign("order_id")->references("order_id")->on("tictail_orders");
			$table->unique("previous_transaction_id");
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
		Schema::drop("tictail_transactions");
	}
}
