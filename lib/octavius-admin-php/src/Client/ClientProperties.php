<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 13.11.18
 * Time: 12:08
 */

namespace OctaviusRocks\Client;


class ClientProperties {

	/**
	 * @return ClientProperties
	 */
	public static function builder() {
		return new ClientProperties();
	}

	/**
	 * @var array
	 */
	private $params = array();

	/**
	 * @return array
	 */
	public function get() {
		return $this->params;
	}

	/**
	 * @param string $key
	 * @param mixed $value
	 *
	 * @return ClientProperties
	 */
	private function set( $key, $value ) {
		$this->params[ $key ] = $value;

		return $this;
	}

	/**
	 * @param $title
	 *
	 * @return ClientProperties
	 */
	public function setTitle( $title ) {
		return $this->set( "title", $title );
	}

	/**
	 * @param $value
	 *
	 * @return ClientProperties
	 */
	public function setDescription( $value ) {
		return $this->set( "description", $value );
	}

	/**
	 * @param array $domains empty array means all domains have access
	 *
	 * @return ClientProperties
	 */
	public function setDomain( $domains = array() ) {
		return $this->set( "domain", ( count( $domains ) > 0 ) ? $domains : array( "*" ) );
	}

	/**
	 * @param $isOff
	 *
	 * @return $this
	 */
	public function setCompressionOff( $isOff ) {
		return $this->set( "compression_off", $isOff );
	}

	/**
	 * @param $operations
	 *
	 * @return $this
	 */
	public function setParallelInsertOperations( $operations ) {
		return $this->set( "max_parallel_inserter_operations", $operations );
	}

	/**
	 * @param $count
	 *
	 * @return $this
	 */
	public function setEventsInsertCount( $count ) {
		return $this->set( "events_insert_count", $count );
	}

	/**
	 * @param $seconds
	 *
	 * @return $this
	 */
	public function setEventsInsertTimeout( $seconds ) {
		return $this->set( "events_insert_timeout_seconds", $seconds );
	}

	/**
	 * @param $seconds
	 *
	 * @return $this
	 */
	public function setQueryCacheExpiration( $seconds ) {
		return $this->set( "query_cache_expiration", $seconds );
	}

	/**
	 * @param $seconds
	 *
	 * @return $this
	 */
	public function setQueryObjectCacheExpiration( $seconds ) {
		return $this->set( "query_object_cache_expiration", $seconds );
	}

	/**
	 * @param $events
	 *
	 * @return $this
	 */
	public function setEventsBudget( $events ) {
		return $this->set( "events_budget", $events );
	}

}