<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 24.07.18
 * Time: 11:20
 */

declare( strict_types=1 );

use OctaviusRocks\Client\ClientProperties;
use PHPUnit\Framework\TestCase;

final class ClientPropertiesTest extends TestCase {

	public function testArgumentsObject(): void {

		$props = ClientProperties::builder()
		                         ->setTitle( "the title" )
		                         ->setDescription( "the description" )
		                         ->setDomain( array( "first", "second" ) )
		                         ->setEventsBudget( 200 )
		                         ->setCompressionOff( true )
		                         ->setEventsInsertCount( 2 )
		                         ->setEventsInsertTimeout( 300 )
		                         ->setParallelInsertOperations( 5 )
		                         ->setQueryCacheExpiration( 6 )
		                         ->setQueryObjectCacheExpiration( 7 );

		$this->assertInstanceOf( '\OctaviusRocks\Client\ClientProperties', $props );
		$this->assertEquals( "the title", $props->get()["title"] );
		$this->assertEquals( "the description", $props->get()["description"] );
		$this->assertEquals( array(
			"first",
			"second",
		), $props->get()["domain"] );
		$this->assertEquals( 200, $props->get()["events_budget"] );
		$this->assertEquals( true, $props->get()["compression_off"] );
		$this->assertEquals( 2, $props->get()["events_insert_count"] );
		$this->assertEquals( 300, $props->get()["events_insert_timeout_seconds"] );
		$this->assertEquals( 5, $props->get()["max_parallel_inserter_operations"] );
		$this->assertEquals( 6, $props->get()["query_cache_expiration"] );
		$this->assertEquals( 7, $props->get()["query_object_cache_expiration"] );

		$props->setDomain();
		$this->assertEquals( array(
			"*",
		), $props->get()["domain"] );

	}

}

