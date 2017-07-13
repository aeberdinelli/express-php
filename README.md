# ExpressPHP
This framework tries to clone the NodeJS [express framework](https://www.npmjs.com/package/express) functionality.

## Install
**Note**: To run ExpressPHP you need PHP >= 7.0 and Apache.

The preferred installation is using Composer: `composer require aeberdinelli/express-php v1.0.1`

## Usage
If you installed using composer, you can just do:

```
<?php
include __DIR__.'/vendor/autoload.php';

use Express\Express;
use Express\Router;

$express = new Express();
$router = new Router();

$router->get('/', function($req, $res) {
	$res->send('hello world!');
});

$express->listen($router);
?>
```

## Routes
Routes are handled using a Router instance, for example:

```
$router = new Router();
$router->get('/', function($req, $res) {
    // This will be called when someone goes to the main page using GET method.
});
```

You can handle post requests as well using post() instead of get().

## Route with dynamic parameters
You can route dynamic URL using parameters, for example:

```
$router = new Router();
$router->get('/:something/:else', function($req, $res) {
    /**
     * Now let's imagine someone enters to URL: /hello/bye, then:
     *
     * $req->params->something will contain 'hello'
     * $req->params->else will contain 'bye'
     */
});
```

## Responses
If you're developing an API for example, you can send json simply doing:

```
$router->post('/', function($req, $res) {
	$res->json(array(
		'error'		=> false,
		'message'	=> 'Hello'
	));
});
```

You can also send a custom http response code using:

```
$router->post('/', function($req, $res) {
	$res->status(201)->json({
		'error'		=> false,
		'message'	=> 'Created!'
	});
});
```

## Template engines
You have avaible [Pug](https://pugjs.org) (ex Jade). Here's an example:

```
// Configure the engine
$express->set('view engine','pug');

// Set the path to the template files
$express->set('views','./views/pug');

// Now you can do something like this
$router->get('/', function($req, $res) {
	$res->render('index.jade');
});

// Or this
$router->get('/users/:username', function($req, $res) {
	$res->render('index.jade', array(
		'name'	=> $req->params->username
	));

	// Now in the template, you can use #{name} to get that variable!
});

```

## Request info
- You have the body of the request in $res->body no matter if you re handling POST or PUT.
- You have all the request headers in $req->headers
