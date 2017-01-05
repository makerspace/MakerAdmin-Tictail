<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Libraries\TictailApi;

use Illuminate\Support\Facades\Storage;

class Tictail extends Controller
{
	/**
	 * Show an overview with orders and their status
	 */
	public function overview(Request $request)
	{
		$tictail = $this->_login();
		$orders = $tictail->getOrderList(0);

		$data = [];
		foreach($orders as $order)
		{
			// *.json file in local storage?
			$filename = "{$order->number}.json";
			$fileExist = Storage::disk("json")->exists($filename);

			$data[] = [
				"tictail"     => $order,
				"storage"     => $fileExist ? $filename : false,
				"instruction" => false,
			];
		}

		// Return json array
		return Response()->json([
			"data" => $data,
			"raw" => $orders
		], 201);
	}

	/**
	 * Download all new Tictail orders
	 */
	public function download(Request $request)
	{
		$tictail = $this->_login();
		$num = $tictail->downloadLatest();

		// Return json array
		return Response()->json([
			"status" => "ok",
			"message" => "Downloaded {$num} orders",
		], 201);
	}

	/**
	 * Get the json data provided by Tictail
	 */
	public function order(Request $request, $order_id)
	{
		$raw = Storage::disk("json")->get("{$order_id}.json");
		$json = json_decode($raw);

		// Return json array
		return Response()->json([
			"data" => $json,
		], 201);
	}

	/**
	 * Show a list of all saves *.json files
	 */
	public function all(Request $request)
	{
		$files = Storage::disk("json")->files();

		// Return json array
		return Response()->json([
			"data" => $files,
		], 201);
	}

	/**
	 *
	 */
	protected function _login()
	{
		$tictail = new TictailAPI();

		if(!empty(config("tictail.bearer")))
		{
			// Use bearer
			$tictail->setBearer(config("tictail.bearer"));
		}
		else
		{
			// Login and get a new bearer
			$tictail->Login(config("tictail.username"), config("tictail.password"));
		}

		// Select store
		$tictail->setStore(config("tictail.store"));

		return $tictail;
	}
}