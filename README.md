# ExpressPHP
This framework tries to clone the NodeJS [ExpressJS framework](https://www.npmjs.com/package/express) functionality.

## Install
**Note**: To run ExpressPHP you need PHP >= 7.0 and Apache.

The preferred installation is using Composer:

`composer require aeberdinelli/express-php v1.1.0`

Then, move the .htaccess to the root of your site and you're done:

`mv vendor/aeberdinelli/express-php/.htaccess ./.htaccess`

## Usage
If you installed using composer, you can just do:

```php
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

```php
$router = new Router();
$router->get('/', function($req, $res) {
    // This will be called when someone goes to the main page using GET method.
});
```

You can handle post requests as well using post() instead of get(). Same for put() and delete().

## Route with dynamic parameters
You can route dynamic URL using parameters, for example:

```php
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

```php
$router->post('/', function($req, $res) {
	$res->json(array(
		'error'		=> false,
		'message'	=> 'Hello'
	));
});
```

You can also send a custom http response code using:

```php
$router->post('/', function($req, $res) {
	$res->status(201)->json({
		'error'		=> false,
		'message'	=> 'Created!'
	});
});
```

**TIP**: There are a few more examples in the `index.php` file in this repository.

## Static files
If you wish to serve static files (likes images, html only) you can use:

```php
// If you visit /static/image.png, this will return the file views/public/image.png
$router->use('/static', $express->static('views/public'));
```

## Template engines
You have avaible [Pug](https://pugjs.org) (ex Jade) and [Mustache](https://mustache.github.io/). Here's an example:

```php
// Configure the engine to Pug
$express->set('view engine','pug');

// Jade was renamed to Pug, but we recognize it ;)
$express->set('view engine','jade');

// Or Mustache
$express->set('view engine','mustache');

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

	// Now in jade, you can use #{name} to get that variable!
});

```

## CSS Precompilers
You can use Less instead of CSS if you want. An example:

```php
use \Express\ExpressLess;

/**
 * Let's say you have a /less folder on your project
 * And you want every request that goes into /css to load the less file within that folder instead
 *
 * In this example /css/global.css will load the compiled version of /less/global.less
 * Same for /css/something.css -> /less/something.less
 */

$less = new ExpressLess($express, array(
	'source'	=> __DIR__.'/less',
	'dest'		=> '/css'
));

// Yes, it's that simple.
```

## Request info
- You have the body of the request in $res->body no matter if you re handling POST or PUT.
- You have the query string under $req->query
- You have the cookies in $req->cookies
- You have all the request headers in $req->headers
