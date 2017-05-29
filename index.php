<?php
include "includes/express.router.php";
include "includes/express.php";

$router = new Router();
$express = new Express();

$router->get('/', function($req, $res) {
	echo "You are using Express PHP!";
});

$express->listen($router);
?>