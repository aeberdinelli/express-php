<?php
class ExpressStatic
{
	private $route;
	private $path;
	private $current;
	
	public function __construct($path, $current = '/')
	{
		$this->current = $current;
		$this->path = $path;
	}
	
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