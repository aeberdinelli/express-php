<?php
namespace Express;

use Express\ExpressStatic;

/**
 * Regex to search for variable names when calling the ->use function in a Router
 *
 * @var string
 */
const REGEX_VAR = '/:([a-zA-Z_\-0-9]+)\//i';

/**
 * Regex to find for a variable sent using the `use` function in a Router.
 * This will be used when getting the var content in an URL
 *
 * @var string
 */
const REGEX_VAR_URL = '([a-zA-Z0-9_\-@]+)/';


class Express
{
	/**
	 * The url we are handling (the value in the ?route querystring sent by htaccess)
	 * @var string
	 */
	private $current;

	/**
	 * The method used in the current request
	 * @var string
	 */
	private $method;

	/**
	 * The headers in the current request
	 * @var array
	 */
	private $headers;

	/**
	 * The parsed body in the current request
	 * @var object
	 */
	private $body;

	/**
	 * The cookies in this request
	 * @var object
	 */
	private $cookies;

	/**
	 * The querystring of this request
	 * @var array
	 */
	private $query;

	/**
	 * Variables avaible within the entire instance and avaible in the template views
	 * @var stdClass
	 */
	public $locals;

	/**
	 * A list of middlewares (aka pending Router instances)
	 * @var array
	 */
	protected $middlewares = array();

	/**
	 * The default settings for ExpressPHP
	 * @var array
	 */
	protected $settings = array(
		/**
		 * Default template engine
		 * @var string
		 */
		'view_engine'	=> '',

		/**
		 * The path where the templates are
		 * @var string
		 */
		'views'			=> '',

		/**
		 * Allow the execution of PHP within the templates?
		 * @var boolean
		 */
		'allow_php'		=> true,

		/**
		 * Make the output of json pretty
		 * @var boolean
		 */
		'pretty_json'	=> false,

		/**
		 * Make the templates render pretty
		 * @var boolean
		 */
		'pretty_print'	=> false,

		/**
		 * Where the cache of templates should be
		 * @var string
		 */
		'cache_dir'		=> '/tmp'
	);

	/**
	 * Gets the info of the current request
	 */
	public function __construct()
	{
		$this->current = $this->parse((isset($_GET['route'])) ? $_GET['route'] : '/');
		$this->method = $_SERVER['REQUEST_METHOD'];
		$this->headers = apache_request_headers();
		$this->cookies = (object) $_COOKIE;
		$this->locals = new \stdClass;

		// Get the querystring, remove the route from it
		parse_str($_SERVER['QUERY_STRING'], $result);
		unset($result['route']);

		$this->query = (object) $result;

		// Obtain the request body
		switch ($this->method)
		{
			case 'POST':
				// TODO: Better check for POST data
				if (count($_POST) > 0)
				{
					// Classic POST
					$this->body = (object) $_POST;
				}
				else
				{
					// JSON POST
					$this->body = json_decode(file_get_contents('php://input'));
				}
			break;
			case 'PUT':
				try
				{
					$this->body = json_decode(file_get_contents('php://input'));
				}
				catch (Exception $e)
				{
					throw new \Exception("Failed to parse PUT body");
					$this->body = (object) array();
				}
			break;
			default:
				$this->body = (object) array();
		}
	}

	/**
	 * Plug-in a middleware (Router)
	 *
	 * @param Router $router An \Express\Router instance
	 * @return void
	 */
	public function use($middleware)
	{
		$this->middlewares[] = $middleware;
	}

	/**
	 * Gets the collected info
	 *
	 * @param bool Return the results instead of dump
	 * @return mixed
	 */
	public function getInfo($return = false)
	{
		$info = array(
			'QueryString'		=> $_SERVER['QUERY_STRING'],
			'ParsedQueryString'	=> $this->query,
			'ParsedURL'			=> $this->current,
			'Headers'			=> $this->headers,
			'Cookies'			=> $this->cookies,
			'Body'				=> $this->body,
			'PHPVersion'		=> phpversion()
		);

		if ($return)
		{
			return $info;
		}
	}

	/**
	 * An alias for $this->setting()
	 */
	public function set($setting, $value)
	{
		return $this->setting($setting, $value);
	}

	/**
	 * Gets or sets a setting
	 *
	 * @param string The variable name
	 * @param string The value of the setting
	 * @return string
	 */
	public function setting($setting, $value = '')
	{
		// View Engine -> view_engine
		$setting = strtolower(str_replace(' ', '_', $setting));

		if ($value != '')
		{
			$this->settings[$setting] = $value;
		}

		return (isset($this->settings[$setting])) ? $this->settings[$setting] : false;
	}

	/**
	 * A helper to make the updates we need to a path
	 *
	 * @param string The path to parse
	 * @return string Valid path
	 */
	private function parse($path)
	{
		if (substr($path, -1) != '/')
		{
			$path .= '/';
		}

		if (substr($path, 0, 1) != '/')
		{
			$path = '/'.$path;
		}

		return $path;
	}

	/**
	 * Serve static files
	 *
	 * @param string The path where the files are
	 * @return ExpressStatic a new instance
	 */
	public function static($path)
	{
		return new ExpressStatic($path, $this->current);
	}

	/**
	 * Make express-php handle the request.
	 *
	 * @param Router An instance of Router (@see \Express\Router)
	 * @param function A function to call after the requests have been handled.
	 * @return void
	 */
	public function listen($router, $callback = null)
	{
		// Prepare middlewares routes
		foreach ($this->middlewares as $middleware)
		{
			$router->use('', $middleware);
		}

		// Get routes from main router
		$routes = $router->getRoutes();
		
		if (!isset($routes[$this->method]))
		{
			throw new \Exception('Could not handle '.$this->method);
		}

		foreach (array_merge($routes['*'], $routes[$this->method]) as $path => $handlers)
		{
			$path = $this->parse($path);

			// Variables to be sent to the handler in the $response var
			$variables = array();

			// Get a list of the expected vars to send them in the $res variable
			preg_match_all(REGEX_VAR, $path, $var_result, PREG_PATTERN_ORDER);

			foreach ($var_result[1] as $var)
			{
				$variables[$var] = '';
			}

			// Build the regex to match the request
			$regex = preg_replace(REGEX_VAR, REGEX_VAR_URL, $path);
			$regex = str_replace('/', '\/', $regex);

			if (preg_match('/^'.$regex.'$/', $this->current))
			{
				// Get a list of the expected vars content
				preg_match_all('/^'.$regex.'$/', $this->current, $body_result, PREG_SET_ORDER);

				$i = 1;
				foreach ($variables as $name => $content)
				{
					if (!empty($body_result[0][$i]))
					{
						$variables[$name] = $body_result[0][$i];
						$i++;
					}
				}

				// Build the $request parameter
				$request = (object) array(
					'settings'	=> $this->settings,
					'path'		=> $this->current,
					'params'	=> (object) $variables,
					'headers'	=> $this->headers,
					'query'		=> $this->query,
					'cookies'	=> $this->cookies,
					'body'		=> $this->body
				);

				// Build the $next parameter
				// TODO: Support more than two handlers for the same route.
				$next = function() {};

				if (count($handlers) > 1)
				{
					$next = $handlers[1];
				}

				// Build the response parameter
				$response = new Response($this->settings);

				// Call the first handler
				$handlers[0]($request, $response, $next);

				// Stop handling this request
				break;
			}
		}

		if (!empty($callback))
		{
			$callback();
		}
	}

	/**
	 * Check if client supports json
	 *
	 * @return bool
	 */
	public static function supportsJSON()
	{
		$headers = apache_request_headers();

		return (isset($headers['Accept']) && strpos($headers['Accept'], 'application/json') > -1);
	}
}
?>
