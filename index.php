<?php
ini_set('display_errors','On');

include "includes/express.static.php";
include "includes/express.response.php";
include "includes/express.router.php";
include "includes/express.php";

$router = new Router();
$express = new Express();

/**
 * Handle the root path / with a json
 */
$router->get('/', function($req, $res) {
	$res->json(array(
		'error'		=> false,
		'message'	=> 'Potato!'
	));
});

/**
 * Handle a put request with a dynamic Id
 */
$router->put('/:id', function($req, $res) {
	$res->json(array(
		'error'		=> false,
		'message'	=> 'Potato #'.$this->params->id.' updated!'
	));
});

/**
 * Handle incoming $_POST data
 */
$router->post('/', function($req, $res) {
	$res->status(201)->json(array(
		'error'		=> false,
		'message'	=> 'Potato with name '.$req->body->name.' created!'
	));
});

/**
 * Handle a delete with a custom http code
 */
$router->delete('/:id', function($req, $res) {
	$res->status(404)->json(array(
		'error'		=> false,
		'message'	=> 'Potato #'.$this->params->id.' not found :('
	));
});

/**
 * Serve static files
 */
$router->use('/src', $express->static('views/public'));

/**
 * Start app!
 */
$express->listen($router);
?>