<?php

return [
	'name'    => 'Tictail import service',
	'version' => '1.0',
	'url'     => 'tictail',
	'gateway' => getenv('APIGATEWAY'),
	'bearer'  => getenv('BEARER'),
];