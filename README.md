# ExpressPHP
This framework tries to clone the NodeJS [express framework](https://www.npmjs.com/package/express) functionality.
If you wish to use this framework, please keep in mind that it's in early development. (PRs welcome)

## Install
To use express-php, just clone this repo and start developing!

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
     * $req->params->else will contain bye
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
})
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

For more examples, check the `index.php` file included in this repository.

## Request info
- You have the body of the request in $res->body no matter if you re handling POST or PUT.
- You have all the request headers in $req->headers