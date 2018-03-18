<?php
namespace App\Libraries;

use App\Libraries\CurlBrowser;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

/**
 */
class TictailApi
{

	protected $curl;

	protected $store_id;

	protected $bearer;

	/**
	 * Constructor
	 */
	function __construct()
	{
		$this->curl = new CurlBrowser();
		$this->Login();
	}

	/**
	 * Destructor
	 */
	function __destruct()
	{
		$this->Logout();
	}

	/**
	 * Sign in and get a bearer
	 */
	public function Login()
	{
		$curl = new CurlBrowser();
		$bearer = getStoredBearer();
		if (!empty($bearer)) {
			$curl->setHeader("Authorization", "Bearer {$bearer}");
			$response = $curl->call("GET", "https://tictail.com/v1.25/me");
			if ($curl->getStatusCode() == 200) {
				$this->setBearer($bearer);
				$logged_in = true;
				return true;
			}
		}
		
		$username = config("tictail.username");
		$password = config("tictail.password");
		
		$curl->useJson();
		$post_data = [
			"email" => $username,
			"password" => $password,
			"scopes" => [
				"store.basic"
			]
		];
		$response_json = $curl->call("POST", "https://tictail.com/identity/sign_in", [], $post_data);
		$response = json_decode($response_json);
		if (isset($response["access_token"])) {
			$bearer = $response["access_token"];
			$this->setStoredBearer($bearer);
			$this->setBearer($bearer);
			return true;
		}
		return false;
	}

	/**
	 * Logout (Invalidate the bearer)
	 *
	 * @todo Not implemented yet
	 */
	public function Logout()
	{
		//TODO: Check if Tictail invalidates user login 'access_tokens' on logout or only on age. 
		return false;
	}

	/**
	 * Get bearer stored in database
	 */
	private function getStoredBearer()
	{
		$result = DB::table("tictail_logins")->where("id", 0)->value("access_token");
		
		return $result;
	}

	/**
	 * Set bearer stored in database
	 */
	private function setStoredBearer($bearer)
	{
		$result = DB::table("tictail_logins")->updateOrInsert([
			"id" => 0
		], [
			"access_token" => $bearer
		]);
	}

	/**
	 * Set a bearer to use with the API
	 */
	public function setBearer($bearer)
	{
		$this->bearer = $bearer;
	}

	/**
	 * Set the store id
	 */
	public function setStore($store_id)
	{
		$this->store_id = $store_id;
	}

	/**
	 * Send an API call and return the data
	 * @todo Logging?
	 */
	public function ApiCall($url, $data = null)
	{
		// Send the request to the server
		$this->curl->setHeader("Authorization", "Bearer {$this->bearer}");
		$this->curl->call("GET", "https://api.tictail.com/v1.5/{$url}");

		// Parse the resulting JSON into an array
		return $this->curl->GetJson();
	}

	/**
	 * Returns a list of orders
	 */
	function getOrderList($start = null, $limit = 50)
	{
		if ($start) {
			$page = "&after={$start}";
		} else {
			$page = "";
		}
		
		return $this->ApiCall("orders?store_id={$this->store_id}&limit={$limit}&order=desc{$page}");
	}

	/**
	 * Get order from Tictail
	 */
	function getOrder($tictail_order_number)
	{
		return $this->ApiCall("orders?store_id={$this->store_id}&number={$tictail_order_number}&expand=items,shipping_address,transactions");
	}

	/**
	 * Returns a list of _all_ orders
	 */
	function getFullOrderList()
	{
		$result = [];

		// Get the first batch of orders
		$data = $this->getOrderList();
		while (! empty($data)) {
			// Loop through orders
			foreach ($data as $row) {
				$result[] = $row;
				$offset = $row->id;
				print_r($row);
			}

			// Get next batch of orders
			$data = $this->getOrderList($offset);
		}

		return $result;
	}

	/**
	 * Do a complete synchronization and download all orders
	 */
	function downloadAll()
	{
		// Get the first batch of orders
		$orders = $this->getOrderList();
		while (! empty($orders)) {
			// Loop through orders
			foreach ($orders as $order) {
				// Order saved in database?
				$order_id = DB::table("tictail_orders")
					->where(["tictail_order_number" => $order->number])
					->value('order_id');
				if ($order_id === null) {
					echo "Downloading {$order->number}\n";
					$this->saveOrder($order->number, $order->id);
				} else {
					echo "No need to download {$filename}\n";
				}
				$offset = $order->id;
			}

			// Get next batch of orders
			$orders = $this->getOrderList($offset);
		}
	}

	/**
	 * Get a list of the latest orders and download a few of them and save in database
	 */
	public function downloadLatest($max = 1)
	{
		$count = 0;
		$orders = $this->getOrderList(0, 1);
		// Loop through orders
		foreach ($orders as $order) {
			// Order saved in database?
			$order_id = DB::table("tictail_orders")
				->where(["tictail_order_number" => $order->number])
				->value('order_id');
			if ($order_id === null) {
				// Download and save in database
				$this->saveOrder($order->number, $order->id);
				$count ++;
			}
			
			if ($count == $max) {
				break;
			}
		}
		
		return $count;
	}

	/**
	 * Download one single order and save as *.json
	 */
	public function saveOrder($order_number, $tictail_order_id)
	{
		$newOrder = $this->getOrder($tictail_order_id);
		$orderData = json_decode($newOrder);
// 		{
// 			"managed":false,
// 			"store_id":"49w",
// 			"locale":"en_US",
// 			"payment_status":"paid",
// 			"number":5509765,
// 			"visible":true,
// 			"id":"znWF",
// 			"prices_include_tax":true,
// 			"user_signed_in":false,
// 			"user_id":"mUBy",
// 			"tax_type":"VAT",
// 			"cart_id":"ZCa2",
// 			"note":null,
// 			"tictail_shopper":true,
// 			"store":{
// 				"country":"SE",
// 				"subdomain":"meduza",
// 				"name":"Erik's Butik",
// 				"id":"49w"
// 			},
// 			"status":"open",
// 			"client_ip":"90.235.5.209",
// 			"attribution":"marketplace:ios",
// 			"grand_total":23900,
// 			"currency":"SEK",
// 			"display_status":"shipped",
// 			"created_at":"2016-07-25T19:17:32.079919",
// 			"modified_at":"2017-02-17T06:51:05.603995",
// 			"store_country":"SE",
// 			"email":"oxen72@hotmail.com",
// 			"fulfillment_status":"fulfilled",
// 			"token":"tt_ord_W7agzJSoiOyjSVEbBjofXkCCHMsj1DvV",
// 			"shipping_address":{
// 				"city":"Stockholm",
// 				"first_name":"Niclas Forslund",
// 				"last_name":null,
// 				"name":"Niclas Forslund",
// 				"zip":"11216",
// 				"phone":null,
// 				"country":"SE",
// 				"line2":"",
// 				"line1":"Hornsbergs strand 49",
// 				"source":"checkout",
// 				"state":null,
// 				"data":{},
// 				"id":"zQuP"
// 			},
// 			"dispute_status":"ineligible"
// 		}
// 			+----------------------+--------------------+------+-----+-------------------+----------------+
// 			| Field                | Type               | Null | Key | Default           | Extra          |
// 			+----------------------+--------------------+------+-----+-------------------+----------------+
// 			| status               | enum(              | NO   | MUL | NULL              |                |
// 											'awaiting_payment','unhandled','pending_dispute','pending_other','finished')
// 			| currency             | enum('SEK')        | NO   |     | NULL              |                |
// 			| order_total          | int(11)            | NO   |     | NULL              |                |
// 			| order_row_id         | int(10) unsigned   | YES  | MUL | NULL              |                |
// 			| transaction_id       | int(10) unsigned   | YES  | MUL | NULL              |                |
// 			| created_at           | datetime           | NO   |     | CURRENT_TIMESTAMP |                |
// 			| modified_at          | datetime           | YES  |     | NULL              |                |
// 			| deleted_at           | datetime           | YES  |     | NULL              |                |
// 			+----------------------+--------------------+------+-----+-------------------+----------------+
		$shippingAddress = $orderData['shipping_address'];
		$member_info = json_encode([
			'email' => $orderData['email'],
			'phone' => $shippingAddress['phone'],
			'note' => $orderData['note'],
			'name' => $shippingAddress['name'],
			'line1' => $shippingAddress['line1'],
			'line2' => $shippingAddress['line2'],
			'zip' => $shippingAddress['zip'],
			'city' => $shippingAddress['city'],
			'country' => $shippingAddress['country'],
			'state' => $shippingAddress['state'],
		]);

		$payment_status = $orderData['payment_status'];
		$status = (
			'cancelled' == $orderData['status']) ? (
				'refunded_fully' == $payment_status ?
					'cancelled_finished':
					'cancelled_awaiting_refund'
			):((/*open (!cancelled)*/
				'unpaid'  == $payment_status || 
				'pending' == $payment_status) ?
					'awaiting_payment':
					'fulfilled' == $orderData['fulfillment_status'] ? 
						'finished' :
						'resolve_member_id'
			);

		$order_id = DB::table("tictail_orders")->insertGetId([
			'tictail_order_number' => $orderData['number'],
			'store_id' => 1, //TODO: ADD store_id, should be obvious from the context, a parameter in this class.
			'member_info' => $member_info,
			'dispute_status' => $orderData['dispute_status'],
			'fulfilled' => $orderData['fulfillment_status']=='fulfilled',
			'status' => $status,
			'currency' => $orderData['currency'],
			'order_total' => $orderData['grand_total'],
			'created_at' => \DateTime::createFromFormat("Y-m-d\TH:i:s.uO", $orderData['created_at']),
			'modified_at' => \DateTime::createFromFormat("Y-m-d\TH:i:s.uO", $orderData['modified_at']),
		]);
	}
}
