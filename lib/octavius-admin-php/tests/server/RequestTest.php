<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 24.07.18
 * Time: 11:20
 */

declare( strict_types=1 );

use OctaviusRocks\Server\ConfigurationsEndpoint;
use OctaviusRocks\Server\Request;
use PHPUnit\Framework\TestCase;


final class RequestTest extends TestCase {

	public function testRequest(): void
	{

		$request = new Request();
		$result = $request->get("http://octavius.local:8081/config/test");

		$this->assertInstanceOf("\OctaviusRocks\Server\Response", $result);
		$this->assertFalse($result->isError());
	}

	public function testServerConfiguration(): void
	{
		$source = ConfigurationsEndpoint::builder("http://octavius.local:8081/config/%s", "%s", Request::builder());
		$this->assertEquals("http://octavius.local:8081/config/test",$source->getServerConfigurationsUrl("test"));

		$clientConfig = $source->getServerConfiguration("test");

		$this->assertInstanceOf("\OctaviusRocks\Server\ServerConfiguration", $clientConfig);


	}

	public function testGetClient(){
		$request = Request::builder()->setAdminSecret("secretadminapikey");
		$client = new \OctaviusRocks\Client\Client(
			"test",
			new \OctaviusRocks\Server\ServerConnection(
				new \OctaviusRocks\Server\ServerConfiguration(
					"octavius.local:8081",
					"/v562/",
					false
				),
				$request
			)
		);
		$props = $client->getProps();

		$this->assertNotNull($props);
	}

}

