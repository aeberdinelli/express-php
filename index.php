<?php
ini_set('display_errors', 'On');

include "vendor/autoload.php";

use Express\Express;
use Express\Router;

$router = new Router();
$express = new Express();

$router->get('/health', function($req, $res) {
	$res->json(array(
		'error'		=> false,
		'status'	=> 'OK'
	));
});

$express->listen($router);
?>
