<?php
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
	private $current;
	private $method;
	private $headers;
	private $body;

	/**
	 * Gets the info of the current request
	 */
	public function __construct() 
	{
		$this->current = $this->parse((isset($_GET['route'])) ? $_GET['route'] : '/');
		$this->method = $_SERVER['REQUEST_METHOD'];
		$this->headers = apache_request_headers();

		// Obtain the request body
		switch ($this->method)
		{
			case 'POST':
				$this->body = (object) $_POST;
			break;
			case 'PUT':
				try
				{
					$this->body = json_decode(file_get_contents('php://input'));
				}
				catch (Exception $e)
				{
					throw new Exception("Failed to parse PUT body");
					$this->body = (object) array();
				}
			break;
			default:
				$this->body = (object) array();
		}
	}

	/**
	 * A helper to make the updated we need to a path
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
	 * Make express-php handle the request.
	 *
	 * @param Router An instance of Router (@see Router)
	 * @param function A function to call after the requests have been handled.
	 * @return void
	 */
	public function listen($router, $callback = null)
	{
		$routes = $router->getRoutes();

		if (!isset($routes[$this->method]))
		{
			die('Could not handle '.$this->method);
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
				$var_regex = '/'.str_replace('/','\/', REGEX_VAR_URL).'/';
				preg_match_all($var_regex, $this->current, $body_result, PREG_PATTERN_ORDER);

				$i = 0;
				foreach ($variables as $name => $content)
				{
					$variables[$name] = $body_result[1][$i];
					$i++;
				}

				// Build the $request parameter
				$request = (object) array(
					'params'	=> (object) $variables,
					'headers'	=> $this->headers,
					'body'		=> $this->body
				);

				// Build the $next parameter
				$next = function() {};

				if (count($handlers) > 1)
				{
					$next = $handlers[1];
				}

				// Build the response parameter
				$response = new Response();

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
}
?>