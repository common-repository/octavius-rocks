<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 24.07.18
 * Time: 11:20
 */

declare( strict_types=1 );

use OctaviusRocks\Client\Client;
use OctaviusRocks\Client\ClientProperties;
use OctaviusRocks\Server\ServerConfiguration;
use OctaviusRocks\Server\Request;
use OctaviusRocks\Server\ServerConnection;
use PHPUnit\Framework\TestCase;

final class ClientTest extends TestCase {

	/**
	 * @var ServerConfiguration
	 */
	var $configuration;

	/**
	 * @var ServerConnection
	 */
	var $clientConnection;

	/**
	 * @var ServerConnection
	 */
	var $noClientConnection;

	function setUp(): void {
		$requestNoClient      = new Request();
		$requestClient       = Request::builder()->setClientSecret("secret" );
		$this->configuration = new ServerConfiguration(
			"octavius.local:8081", "/v562/", false
		);

		$this->noClientConnection = new ServerConnection( $this->configuration, $requestNoClient );
		$this->clientConnection   = new ServerConnection( $this->configuration, $requestClient );
	}

	public function testCreateClientFail() {
		$response = $this->noClientConnection->createClient(ClientProperties::builder()->setTitle("unittest"));
		$this->assertNull($response);
		$response = $this->clientConnection->createClient(ClientProperties::builder()->setTitle("unittest"));
		$this->assertNull($response);
	}


	


}

