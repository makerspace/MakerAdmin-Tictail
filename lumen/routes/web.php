<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$app->get("tictail/overview",   "Tictail@overview");
$app->get("tictail/download",   "Tictail@download");
$app->get("tictail/all",        "Tictail@all");
$app->get("tictail/order/{id}", "Tictail@order");