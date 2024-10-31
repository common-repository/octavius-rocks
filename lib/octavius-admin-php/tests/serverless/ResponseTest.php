<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 24.07.18
 * Time: 11:20
 */

declare( strict_types=1 );

use OctaviusRocks\Server\Response;
use PHPUnit\Framework\TestCase;

final class ResponseTest extends TestCase {

	public function testIsErrorResponseObject(): void
	{
		$this->assertInstanceOf('\OctaviusRocks\Server\Response', Response::parse(null));
		$this->assertTrue(Response::parse(null)->isError());
		$this->assertInstanceOf('\OctaviusRocks\Server\Response', Response::parse(false));
		$this->assertTrue(Response::parse(null)->isError());
		$this->assertInstanceOf('\OctaviusRocks\Server\Response', Response::parse(""));
		$this->assertTrue(Response::parse(null)->isError());
		$this->assertInstanceOf('\OctaviusRocks\Server\Response', Response::parse("invalid json"));
		$this->assertTrue(Response::parse(null)->isError());
		$this->assertInstanceOf('\OctaviusRocks\Server\Response', Response::parse("{'error':true }"));
		$this->assertTrue(Response::parse("{'error':true }")->isError());
	}


	public function testIsSuccessResponseObject(): void
	{
		// true is a valid response with no payload
		$this->assertInstanceOf('\OctaviusRocks\Server\Response', Response::parse("true"));
		$this->assertFalse(Response::parse("true")->isError());

		$this->assertInstanceOf('\OctaviusRocks\Server\Response', Response::parse(true));
		$this->assertFalse(Response::parse(true)->isError());

		// json string with error false
		$this->assertInstanceOf('\OctaviusRocks\Server\Response', Response::parse("{'error':false }"));
		$this->assertFalse(Response::parse(true)->isError());

		// complex response
		$json = json_encode(array(
			"error" => false,
			"payload1" => true,
			"payload2" => 'true',
			"payload3" => array(1,2,3),
			"payload4" => array(
				"test" => "yes",
			)
		));
		$response = Response::parse($json);
		$this->assertInstanceOf('\OctaviusRocks\Server\Response', $response);
		$this->assertFalse($response->isError());

		$this->assertEquals(true, $response->getPayload()["payload1"]);
		$this->assertEquals('true', $response->getPayload()["payload2"]);
		$this->assertEquals(array(1,2,3), $response->getPayload()["payload3"]);
		$this->assertEquals(array("test" => "yes"), $response->getPayload()["payload4"]);

	}

	public function testIsSuccessComplexResponseObject(): void
	{

		// complex response
		$json = json_encode(array(
			"error" => false,
			"payload1" => true,
			"payload2" => 'true',
			"payload3" => array(1,2,3),
			"payload4" => array(
				"test" => "yes",
			)
		));
		$response = Response::parse($json);
		$this->assertInstanceOf('\OctaviusRocks\Server\Response', $response);
		$this->assertFalse($response->isError());

		$this->assertEquals(true, $response->getPayload()["payload1"]);
		$this->assertEquals('true', $response->getPayload()["payload2"]);
		$this->assertEquals(array(1,2,3), $response->getPayload()["payload3"]);
		$this->assertEquals(array("test" => "yes"), $response->getPayload()["payload4"]);

	}
}

