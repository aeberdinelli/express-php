<?php
namespace Express;

use Express\{Express, ExpressStatic, Router};

class ExpressLess
{
	/**
	 * Current express instance
	 * @var Express
	 */
	protected $express;

	/**
	 * An Express\Router instance
	 * @var Router
	 */
	protected $router;

	/**
	 * Less files folder
	 * @var String
	 */
	protected $source;

	/**
	 * URL to serve from
	 * @var String
	 */
	protected $output;

	/**
	 * A map with the path and destination
	 * @var Array
	 */
	protected $map = array();

	/**
	 * Output extension
	 * @var String
	 */
	protected $outputExt = 'css';

	/**
	 * Mime-type for output css
	 * @var String
	 */
	const MIME_TYPE = 'text/css';

	/**
	 * Less extensions supported
	 * @var Array
	 */
	const LESS_EXT = array('less');

	/**
	 * Constructor
	 * 
	 * @param Express $app Requires a running Express instance to find URL
	 * @param array $options Options
	 */
	public function __construct(Express $app, array $options)
	{
		if (empty($app))
		{
			throw new \Exception("ExpressLess needs an Express instance");
		}

		extract($options);

		if (!isset($source) || !isset($dest))
		{
			throw new \Exception("ExpressLess needs both source and dest in options");
		}

		$this->express = $app;
		$this->source = $this->normalizePath($source);
		$this->output = $dest;

		if (isset($outputExt))
		{
			$this->outputExt = $outputExt;
		}

		// Add some settings to Express
		$this->express->set('less folder', $this->source);
		$this->express->set('less output extension', $this->outputExt);

		$this->initialize();
	}

	/**
	 * Find all the files within the source folder
	 */
	private function initialize()
	{
		// Create a new router
		$this->router = new Router();

		try
		{
			$dir = opendir($this->source);

			while ($file = readdir($dir))
			{
				if (!is_dir($this->source.'/'.$file))
				{
					if (in_array(ExpressStatic::getExtension($file), self::LESS_EXT))
					{
						// File is supported, add to the router
						$name = $this->updateExtension($file);

						$this->router->get($this->output.'/'.$name, function($req, $res) {
							\Express\ExpressLess::compile($req, $res);
						});
					}
				}
			}

			// Add router to Express
			$this->express->use($this->router);

			closedir($dir);
		}
		catch (Exception $e)
		{
			throw new \Exception("Could not analyze less source dir");
		}
	}

	/**
	 * Normalize a path
	 *
	 * @param String $path Path to normalize
	 * @return String
	 */
	private function normalizePath(string $path)
	{
		return str_replace('/', DIRECTORY_SEPARATOR, $path);
	}

	/**
	 * Update extension from .less to .css (or configured output ext)
	 *
	 * @param String $file Name of the file
	 * @return String
	 */
	private function updateExtension(string $file)
	{
		return preg_replace('/\.('.implode('|', self::LESS_EXT).')$/i', '.'.$this->outputExt, $file);
	}

	/**
	 * Compile a less file and send it as a response
	 */
	public static function compile($req, $res)
	{
		// Remove ending /
		$path = preg_replace('/\/$/', '', $req->path);

		// Extract file from url
		$ext = $req->settings['less_output_extension'];

		// Get filename
		preg_match_all('/\/?([a-z_-]+)\.'.$ext.'$/i', $path, $parts);

		if (!empty($parts[1][0]))
		{
			$name = $parts[1][0];
			$src = $req->settings['less_folder'].DIRECTORY_SEPARATOR.$name.'.less';

			// Compile and send!
			if (file_exists($src))
			{
				$parser = new \Less_Parser();
				$parser->parseFile($src);

				$res->header('Content-Type', self::MIME_TYPE);
				return $res->send($parser->getCss());
			}
		}
		
		return $res->status(500)->send('Error processing less file');
	}
}
?>