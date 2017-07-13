<?php
namespace Express;

/**
 * This class is called automatically when handling a request and shouldn't be called directly
 */

class ExpressStatic
{
	/**
	 * The current route we are handling
	 * @var string
	 */
	private $route;

	/**
	 * The path to the static files
	 * @var string
	 */
	private $path;

	/**
	 * The current route without the root location
	 * @var string
	 */
	private $current;

	/**
	 * Constructor
	 *
	 * @param string The path where the static files are
	 * @param string The current route
	 * @return void
	 */
	public function __construct($path, $current = '/')
	{
		$this->current = $current;
		$this->path = $path;
	}

	/**
	 * A helper to obtain the file path for the requested one
	 *
	 * @param string The root of the directory
	 * @param boolean To append index.html or not
	 * @return string The generated path
	 */
	private function parse($route, $index = false)
	{
		$route = str_replace($route, '', $this->current);

		if (substr($route, -1) != '/')
		{
			$route .= '/';
		}

		if (substr($route, 0, 1) != '/')
		{
			$route = '/'.$route;
		}

		if ($index && substr($route, -1) == '/')
		{
			$route .= 'index.html';
		}

		return $route;
	}

	/**
	 * Prepares the route and returns the file content
	 *
	 * @param string The current url
	 * @return mixed
	 */
	public function init($route)
	{
		if (preg_match('/^'.str_replace('/','\/', $route).'/', $this->current))
		{
			if (file_exists($this->path.$this->parse($route, true)))
			{
				die(file_get_contents($this->path.$this->parse($route, true)));
			}

			// Not Found
		}
	}
}
?>
