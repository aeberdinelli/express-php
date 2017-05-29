<?php
class Response
{
	private $sent;
	private $headers;

	public function __construct($context = null)
	{
		$this->sent = false;
		$this->headers = array();
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

		echo json_encode($body);
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