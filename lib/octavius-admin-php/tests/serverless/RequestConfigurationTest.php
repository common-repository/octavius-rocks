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

final class RequestConfigurationTest extends TestCase {



	public function testSimpleConfiguration(): void
	{
		$config = new \OctaviusRocks\Server\RequestConfiguration("http://octavius.rocks");

		$this->assertEquals("http://octavius.rocks", $config->getUrl());
		$this->assertEquals(\OctaviusRocks\Server\RequestConfiguration::DEFAULT_TIMEOUT, $config->getTimeout());

	}

	public function testTimeoutConfiguration(): void
	{
		\OctaviusRocks\Server\RequestConfiguration::setGlobalTimeout(11);
		$config = new \OctaviusRocks\Server\RequestConfiguration("http://octavius.rocks");

		$this->assertEquals(11, $config->getTimeout());
		$config->setTimeout(22);
		$this->assertEquals(22, $config->getTimeout());

	}
}

