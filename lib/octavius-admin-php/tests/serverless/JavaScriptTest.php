<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 24.07.18
 * Time: 11:20
 */

declare( strict_types=1 );

use OctaviusRocks\Server\JavaScript;
use PHPUnit\Framework\TestCase;

final class JavaScriptTest extends TestCase {

	public function testCheckCoreUrl(): void
	{
		$configuration = new \OctaviusRocks\Server\ServerConfiguration(
			"octavius.rocks",
			"/v562/",
			true
		);

		$js = new JavaScript($configuration);

		$this->assertEquals(
			"https://octavius.rocks/v562/files/core.js",
			$js->core()
		);

		$this->assertEquals(
			"https://octavius.rocks/v562/files/core.slim.js",
			$js->core(true)
		);
	}

	public function testCheckSocketIOUrl(): void
	{
		$configuration = new \OctaviusRocks\Server\ServerConfiguration(
			"octavius.rocks",
			"/v562/",
			true
		);

		$js = new JavaScript($configuration);

		$this->assertEquals(
			"https://octavius.rocks/v562/socket.io/socket.io.js",
			$js->socketIO()
		);

		$this->assertEquals(
			"https://octavius.rocks/v562/files/socket.io.slim.js",
			$js->socketIO(true)
		);
	}

	public function testCheckQueryUrl(): void
	{
		$configuration = new \OctaviusRocks\Server\ServerConfiguration(
			"octavius.rocks",
			"/v562/",
			true
		);

		$js = new JavaScript($configuration);

		$this->assertEquals(
			"https://octavius.rocks/v562/files/query.js",
			$js->query()
		);

		$this->assertEquals(
			"https://octavius.rocks/v562/files/query.slim.js",
			$js->query(true)
		);
	}

	public function testCheckTrackerUrl(): void
	{
		$configuration = new \OctaviusRocks\Server\ServerConfiguration(
			"octavius.rocks",
			"/v562/",
			true
		);

		$js = new JavaScript($configuration);

		$this->assertEquals(
			"https://octavius.rocks/v562/files/tracker.js",
			$js->tracker()
		);

		$this->assertEquals(
			"https://octavius.rocks/v562/files/tracker.slim.js",
			$js->tracker(true)
		);
	}
}

