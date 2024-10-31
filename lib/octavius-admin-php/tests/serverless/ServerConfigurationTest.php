<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 24.07.18
 * Time: 11:20
 */

declare( strict_types=1 );

use OctaviusRocks\Server\ServerConfiguration;
use OctaviusRocks\Server\ConfigurationsEndpoint;
use OctaviusRocks\Server\Request;
use OctaviusRocks\Server\Response;
use PHPUnit\Framework\TestCase;

final class ServerConfigurationTest extends TestCase {

	public function testServerConfigurationPath(): void {
		$config = new ServerConfiguration(
			"service.octavius.rocks", "/v56/"
		);
		$this->assertEquals(
			"https://service.octavius.rocks/v56/test",
			$config->getUrl( "test" )
		);
	}

	public function testServerConfigurationPathNoSsl(): void {
		$config = new ServerConfiguration(
			"service.octavius.rocks", "/v56/", false
		);
		$this->assertEquals(
			"http://service.octavius.rocks/v56/test",
			$config->getUrl( "test" )
		);
	}

	public function testServerConfigurationHandler(): void
	{
		$requestMock = $this->createMock(Request::class);
		$requestMock->method('getMaybeAdmin')->willReturn(new Response(false, array(
			"server"=> "service.octavius.rocks",
			"path" => '/v562/',
			'useHttps' => false,
			"version" => array(
				"id" => 562,
				"readable" => "v5.6.2",
			)
		)));


		$configurations = ConfigurationsEndpoint::builder(
			"https://service.octavius.rocks/config/%apikey%",
			"%apikey%",
			$requestMock
		);

		$this->assertEquals(
			"https://service.octavius.rocks/config/test",
			$configurations->getServerConfigurationsUrl("test")
		);

		$config = $configurations->getServerConfiguration("test");
		$this->assertInstanceOf(
			'\OctaviusRocks\Server\ServerConfiguration',
			$config
		);

		$this->assertEquals("service.octavius.rocks", $config->getServer());
		$this->assertEquals("/v562/", $config->getVersionPath());
		$this->assertEquals(562, $config->getVersion());
		$this->assertEquals("v5.6.2", $config->getVersionName());
	}



}

