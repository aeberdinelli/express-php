<?php
ini_set('display_errors','On');

include "includes/express.response.php";
include "includes/express.router.php";
include "includes/express.php";

$router = new Router();
$express = new Express();

$router->get('/', function($req, $res) {
	$res->json(array(
		'error'		=> false,
		'message'	=> 'Potato!'
	));
});

$router->put('/', function($req, $res) {
	$res->json(array(
		'error'		=> false,
		'message'	=> 'Potato updated!'
	));
});

$router->post('/', function($req, $res) {
	$res->status(201)->json(array(
		'error'		=> false,
		'message'	=> 'Potato created!'
	));
});

$router->delete('/', function($req, $res) {
	$res->json(array(
		'error'		=> false,
		'message'	=> 'Potato removed :('
	));
});

$express->listen($router);
?>