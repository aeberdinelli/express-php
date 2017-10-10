<?php
use PHPUnit\Framework\TestCase;
use Express\{Express, ExpressStatic};

class ExpressTest extends TestCase
{
	protected $express;

	protected function setUp()
	{
		// Mock environment
		$_SERVER['REQUEST_METHOD'] = 'GET';
		$_SERVER['QUERY_STRING'] = '';

		$this->express = new Express();
	}

	public function testGetInfo()
	{
		$info = $this->express->getInfo(true);

		$this->assertArrayHasKey('QueryString', $info);
		$this->assertArrayHasKey('ParsedQueryString', $info);
		$this->assertArrayHasKey('ParsedURL', $info);
		$this->assertArrayHasKey('Headers', $info);
		$this->assertArrayHasKey('Cookies', $info);
		$this->assertArrayHasKey('Body', $info);
		$this->assertArrayHasKey('PHPVersion', $info);
	}

	/**
	 * @covers Express::set
	 */
	public function testSetting()
	{
		$this->assertEquals(
			'value',
			$this->express->setting('A setting', 'value')
		);

		$this->assertEquals(
			'value',
			$this->express->setting('a_setting')
		);
	}

	public function testStatic()
	{
		$this->assertInstanceOf(
			ExpressStatic::class,
			$this->express->static('/static')
		);
	}
}
?>