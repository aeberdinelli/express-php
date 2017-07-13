<?php
namespace Express;

/**
 * Generates the routing map to be handled
 *
 */

class Router
{
	/**
	 * An array containing the method, path and the list of handlers
	 * @var array
	 */
	private $map;

	/**
	 * Constructor
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->map = array(
			'POST'		=> array(),
			'GET'		=> array(),
			'PUT'		=> array(),
			'DELETE'	=> array(),
			'*'			=> array()
		);
	}

	/**
	 * Adds a handler for a route
	 *
	 * @param string The route to be handled
	 * @param function The function to be executed when the route and method matches
	 * @param string The method (POST, PUT, ...)
	 * @return void
	 */
	public function use($route, $callback = null, $map = '*')
	{
		// Handle a call with a router
		if (is_a($callback, 'Router'))
		{
			$routes = $callback->getRoutes();

			foreach ($routes as $method => $handlers)
			{
				foreach ($handlers as $path => $handler)
				{
					if (!isset($this->map[$method][$path]))
					{
						$this->map[$method][$path] = array();
					}

					$this->map[$method][$path][] = $handler;
				}
			}
		}

		// Handle static files
		elseif (is_a($callback, 'ExpressStatic'))
		{
			$callback->init($route);
		}

		// Handle a call with a custom handler
		else
		{
			if (!isset($this->map[$map][$route]))
			{
				$this->map[$map][$route] = array();
			}

			$this->map[$map][$route][] = $callback;
		}
	}

	/**
	 * Adds a handler for a route in the GET method
	 *
	 * @param string The route to be handled
	 * @param function The function to be executed when the route and method matches
	 * @return void
	 */
	public function get($route, $callback = null)
	{
		$this->use($route, $callback, 'GET');
	}

	/**
	 * Adds a handler for a route in the POST method
	 *
	 * @param string The route to be handled
	 * @param function The function to be executed when the route and method matches
	 * @return void
	 */
	public function post($route, $callback = null)
	{
		$this->use($route, $callback, 'POST');
	}

	/**
	 * Adds a handler for a route in the PUT method
	 *
	 * @param string The route to be handled
	 * @param function The function to be executed when the route and method matches
	 * @return void
	 */
	public function put($route, $callback = null)
	{
		$this->use($route, $callback, 'PUT');
	}

	/**
	 * Adds a handler for a route in the DELETE method
	 *
	 * @param string The route to be handled
	 * @param function The function to be executed when the route and method matches
	 * @return void
	 */
	public function delete($route, $callback = null)
	{
		$this->use($route, $callback, 'DELETE');
	}

	/**
	 * Returns the current mapping
	 *
	 * @return array
	 */
	public function getRoutes()
	{
		return $this->map;
	}
}
?>
