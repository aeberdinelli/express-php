# ExpressPHP
This framework tries to clone the NodeJS [express framework](https://www.npmjs.com/package/express) functionality.
If you wish to use this framework, please keep in mind that it's in early development. (PRs welcome)

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
     * Now you have that variables inside $req->params:
     *
     * $req->params->something
     * $req->params->else
     */
});
```
