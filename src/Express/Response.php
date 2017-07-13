<?php
namespace Express;

use Jade\Jade;

/**
 * This class is called automatically when handling a request and shouldn't be called directly
 */

class Response
{
	private $sent;
	private $headers;
	private $settings;

	/**
	 * An instance of the view engine (for now only Jade supported)
	 */
	private $engine;

	/**
	 * Constructor
	 *
	 * @param array The default express settings
	 * @return void
	 */
	public function __construct($settings = array())
	{
		$this->sent = false;
		$this->headers = array();
		$this->settings = $settings;

		if (in_array($this->settings['view_engine'], array('jade','pug')))
		{
			try
			{
				$this->engine = new Jade($this->settings['cache_dir'], $this->settings['pretty_print']);
			}
			catch (Exception $e)
			{
				throw new \Exception("Could not instantiate template engine");
			}
		}
	}

	/**
	 * Sets the status code of the response
	 *
	 * @param int The HTTP code
	 * @return Response
	 */
	public function status($code)
	{
		http_response_code($code);

		return $this;
	}

	/**
	 * Send a response with JSON
	 *
	 * @param array The content to send
	 * @return void
	 */
	public function json($body)
	{
		$this->header("Content-Type", "application/json");
		$this->headers();

		if ($this->settings['pretty_json'])
		{
			echo json_encode($body, JSON_PRETTY_PRINT);
		}
		else
		{
			echo json_encode($body);
		}
	}

	/**
	 * Render a template using the configured view engine
	 *
	 * @param string Path of the template
	 * @param array Variables to be put into the view
	 * @return void
	 */
	public function render($path, $scope = array())
	{
		if ($this->settings['view_engine'] == '')
		{
			throw new \Exception("There is no engine configured for this view");
		}

		if (!file_exists($this->settings['views'].'/'.$path))
		{
			throw new \Exception("The template ".$this->settings['views']."/".$path." does not exist");
		}

		if ($this->settings['allow_php'])
		{
			// A not so pretty little hack to send the variables to the view
			eval('
				$scope = json_decode(\''.json_encode($scope).'\', true);
				extract($scope);
				?>
			'.$this->engine->render($this->settings['views'].'/'.$path, $scope));
		}
		else
		{
			echo $this->engine->render($this->settings['views'].'/'.$path, $scope);
		}
	}

	/**
	 * Send the response headers
	 *
	 * @return void
	 */
	private function headers()
	{
		if (headers_sent())
		{
			return;
		}

		foreach ($this->headers as $header => $content)
		{
			header($header.': '.$content);
		}
	}

	/**
	 * Sets a header
	 *
	 * @param string header
	 * @param string content
	 * @return void
	 */
	public function header($header, $content)
	{
		$this->headers[$header] = $content;
	}

	/**
	 * Send a response with a String
	 *
	 * @param string Response
	 * @return void
	 */
	public function send($body)
	{
		echo $body;
	}
}
?>
