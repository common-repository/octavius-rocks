<?php

use OctaviusRocks\Client\ClientProperties;
use OctaviusRocks\Server\Exceptions\AdminSecretException;
use OctaviusRocks\Server\Request;
use OctaviusRocks\Server\Response;
use OctaviusRocks\Server\ServerConfiguration;
use OctaviusRocks\Server\ServerConnection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Created by PhpStorm.
 * User: edward
 * Date: 16.11.18
 * Time: 19:55
 */
class ServerConnectionTest extends TestCase {

	/**
	 * @var ServerConfiguration
	 */
	private $serverConfiguration;
	/**
	 * @var MockObject
	 */
	private $requestMock;

	function setUp(): void {
		$this->serverConfiguration = new ServerConfiguration("server", "versionpath");
		$this->requestMock             = $this->createMock( Request::class );
	}

	public function testCreateClient() {
		$this->requestMock->method( "postAdmin" )
		                  ->willReturn( new Response( false, array( "id" => "test" ) ) );
		$connection = new ServerConnection( $this->serverConfiguration, $this->requestMock );
		$id         = $connection->createClient( ClientProperties::builder() );
		$this->assertEquals( "test", $id );
	}

	public function testCreateClientFail() {
		$this->requestMock->method( "postAdmin" )
		                  ->willThrowException( new AdminSecretException( "test" ) );
		$connection = new ServerConnection( $this->serverConfiguration, $this->requestMock );
		$id         = $connection->createClient( ClientProperties::builder() );
		$this->assertNull( $id );
	}

	public function testGetClient() {
		$this->requestMock->method( "getMaybeAdmin" )
		                  ->willReturn( new Response( false, array( "title" => "test" ) ) );
		$connection = new ServerConnection( $this->serverConfiguration, $this->requestMock );
		$result     = $connection->getClient( "apikey" );
		$this->assertInstanceOf("\OctaviusRocks\Server\Response", $result);
		$this->assertTrue($result->isSuccess());
		$this->assertEquals(array("title" => "test"), $result->getPayload());
	}

	public function testGenerateSecret(): void{
		$expectedSecret = "test-secret";
		$this->requestMock->method( "postAdmin" )
		                  ->willReturn( new Response( false, array( "secret" => $expectedSecret ) ) );
		$connection = new ServerConnection( $this->serverConfiguration, $this->requestMock );

		$secret = $connection->generateClientSecret("test-api-key");

		$this->assertEquals($expectedSecret, $secret);
	}

	public function testGenerateSecretError(): void{
		$expectedSecret = "test-secret";
		$this->requestMock->method( "postAdmin" )
		                  ->willReturn( new Response( true, array( "msg" => "some message" ) ) );
		$connection = new ServerConnection( $this->serverConfiguration, $this->requestMock );

		$secret = $connection->generateClientSecret("test-api-key");
		$this->assertNull($secret);
	}

}