<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 24.07.18
 * Time: 11:20
 */

declare( strict_types=1 );

use OctaviusRocks\Client\ClientProperties;
use OctaviusRocks\OQL\Field;
use OctaviusRocks\Server\ServerConfiguration;
use OctaviusRocks\Server\Request;
use OctaviusRocks\Server\Response;
use OctaviusRocks\Server\ServerConnection;
use PHPUnit\Framework\TestCase;

final class ClientQueryTest extends TestCase {

	public function testArgumentsObject(): void {

		$requestMock = $this->createMock( Request::class );
		$requestMock->method( 'postMaybeAdmin' )
		            ->willReturn( new Response( false, array() ) );


		$server = new ServerConnection(
			new ServerConfiguration(
				"https://..",
				"/v562/"
			),
			$requestMock
		);

		$client = new OctaviusRocks\Client\Client(
			"my-api-key",
			$server
		);

		$builder = \OctaviusRocks\OQL\Arguments::builder();
		$response = $client->query( $builder->addField( $builder->buildField( Field::HITS ) ) );

		$this->assertFalse( $response->isError() );


	}

}

