<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Libraries\CurlBrowser;

use App\Libraries\TictailApi;

class TictailDownload extends Command
{
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $signature = "tictail:download";

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = "Download latest orders from Tictail";

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function fire()
	{
// 		$this->info("Tictail download");

// 		$tictail = new TictailAPI();

// 		if(!empty(config("tictail.bearer")))
// 		{
// 			// Use bearer
// 			$tictail->setBearer(config("tictail.bearer"));
// 		}
// 		else
// 		{
// 			// Login and get a new bearer
// 			$tictail->Login();
// 		}

// 		// Select store
// 		$tictail->setStore(config("tictail.store"));

// 		$orders = $tictail->downloadAll();
	}
}