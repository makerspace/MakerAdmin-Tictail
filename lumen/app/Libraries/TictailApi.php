<?php
namespace App\Libraries;

use App\Libraries\CurlBrowser;
use Illuminate\Support\Facades\Storage;

/**
 *
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
		$this->curl = new CurlBrowser;
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
	 * @todo Not implemented yet
	 */
	public function Login($username, $password)
	{
		return false;
	}

	/**
	 * Logout (Invalidate the bearer)
	 * @todo Not implemented yet
	 */
	public function Logout()
	{
		return false;
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
		$this->curl->call("GET", "https://api.tictail.com/v1/{$url}");

		// Parse the resulting JSON into an array
		return $this->curl->GetJson();
	}

	/**
	 * Returns a list of orders
	 */
	function getOrderList($start = null, $limit = 50)
	{
		if($start)
		{
			$page = "&after={$start}";
		}
		else
		{
			$page = "";
		}

		return $this->ApiCall("stores/{$this->store_id}/orders?limit={$limit}&order=desc{$page}");
	}

	/**
	 * Get order from Tictail
	 */
	function getOrder($order_id)
	{
		return $this->ApiCall("stores/{$this->store_id}/orders/{$order_id}");
	}

	/**
	 * Returns a list of _all_ orders
	 */
	function getFullOrderList()
	{
		$result = [];

		// Get the first batch of orders
		$data = $this->getOrderList();
		while(!empty($data))
		{
			// Loop through orders
			foreach($data as $row)
			{
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
		while(!empty($orders))
		{
			// Loop through orders
			foreach($orders as $order)
			{
				$filename = "{$order->number}.json";
				if(!Storage::disk("json")->exists($filename))
				{
					echo "Downloading {$filename}\n";
					$this->downloadOrder($order->number, $order->id);
				}
				else
				{
					echo "No need to download {$filename}\n";
				}
				$offset = $order->id;
			}

			// Get next batch of orders
			$orders = $this->getOrderList($offset);
		}
	}

	/**
	 * Get a list of the latest orders and download a few of them and save as *.json
	 */
	public function downloadLatest($max = 20)
	{
		$count = 0;
		$orders = $this->getOrderList(0, 60);
		// Loop through orders
		foreach($orders as $order)
		{
			// *.json file in local storage?
			$filename = "{$order->number}.json";
			if(!Storage::disk("json")->exists($filename))
			{
				// Download and save file
				$this->downloadOrder($order->number, $order->id);
				$count++;
			}

			if($count == $max)
			{
				break;
			}
		}

		return $count;
	}

	/**
	 * Download one single order and save as *.json
	 */
	public function downloadOrder($order_number, $order_id)
	{
		$newOrder = $this->getOrder($order_id);
		$this->_writeFile("{$order_number}.json", $newOrder);
	}

	/**
	 * Save json data from Tictail to local storage
	 */
	protected function _writeFile($filename, $data)
	{
		$str = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
		$str .= "\n";
		Storage::disk("json")->put($filename, $str);
	}
}