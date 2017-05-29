<?php
class Router
{
	private $map;

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

	public function use($route, $callback = null, $map = '*')
	{
		// Handle a call with a path
		if (!$route instanceof Router)
		{
			if (!isset($this->map[$map][$route]))
			{
				$this->map[$map][$route] = array();
			}

			$this->map[$map][$route][] = $callback;
		}

		// Handle a call with a Router
		else
		{
			$routes = $route->getRoutes();

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
	}

	public function get($route, $callback = null, $map = 'GET')
	{
		$this->use($route, $callback, $map);
	}

	public function post($route, $callback = null, $map = 'POST')
	{
		$this->use($route, $callback, $map);
	}

	public function delete($route, $callback = null, $map = 'DELETE')
	{
		$this->use($route, $callback, $map);
	}

	public function getRoutes()
	{
		return $this->map;
	}
}
?>