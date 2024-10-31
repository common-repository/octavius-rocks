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

final class AdminTest extends TestCase {

	/**
	 * @var ServerConfiguration
	 */
	var $configuration;
	/**
	 * @var ServerConnection
	 */
	var $adminConnection;
	/**
	 * @var ServerConnection
	 */
	var $noAdminConnection;

	function setUp() : void {
		$requestNoAdmin      = new Request();
		$requestAdmin        = Request::builder()->setAdminSecret("secretadminapikey" );
		$this->configuration = new ServerConfiguration(
			"octavius.local:8081", "/v562/", false
		);

		$this->noAdminConnection = new ServerConnection( $this->configuration, $requestNoAdmin );
		$this->adminConnection   = new ServerConnection( $this->configuration, $requestAdmin );
	}

	public function testCreateClientFail() {
		$response = $this->adminConnection->createClient(ClientProperties::builder());
		$this->assertInternalType('string', $response );
	}

	public function testCreateClientSuccess() {
		$response = $this->adminConnection->createClient(
			ClientProperties::builder()
			                ->setTitle( "TestClient " . date( "H:i:s-dmY" ) )
			                ->setDescription( "Test description" )
		);
		$this->assertInternalType("string", $response );
	}

	public function testGetProps(): void {

		// not admin authorized
		$client = new Client( "test", $this->noAdminConnection );
		$this->assertEquals( NULL, $client->getProps() );

		// admin authorized
		$client = new Client( "test", $this->adminConnection );
		$props  = $client->getProps();
		$this->assertNotNull( $props );

		$this->assertGreaterThan( 2, $props );

	}

	public function testSaveProps(): void {

		// admin authorized
		$client = new Client( "test", $this->adminConnection );
		$props  = $client->getProps();
		$this->assertNotNull( $props );
		$initialTitle = $props["title"];
		$success      = $client->setProps( ClientProperties::builder()
		                                                   ->setTitle( "Test1" ) );

		$this->assertTrue( $success );

		$props    = $client->getProps();
		$newtitle = $props["title"];
		$this->assertEquals( "Test1", $newtitle );

		$success = $client->setProps( ClientProperties::builder()
		                                     ->setTitle( $initialTitle ) );

		$this->assertTrue( $success );
	}


}

