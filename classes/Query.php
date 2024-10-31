<?php

namespace OctaviusRocks;

use OctaviusRocks\Model\CacheConfig;
use OctaviusRocks\OQL\Arguments;

class Query {

	/**
	 * @var Query
	 */
	private static $instance;

	/**
	 * @return Query
	 */
	static function get_instance() {
		return self::$instance;
	}

	/**
	 * @var QueryServerRequest
	 */
	private $queryServerRequest;

	/**
	 * @var mixed|null
	 */
	private $cache;

	/**
	 * @var null|array
	 */
	private $data;

	/**
	 * @var string
	 */
	private $dataSource;

	const DATA_SOURCE_NONE = "";
	const DATA_SOURCE_CACHE = "cache";
	const DATA_SOURCE_SERVER_WITH_CACHE_UPDATE = "server_with_cache_update";
	const DATA_SOURCE_SERVER = "server";

	/**
	 * Query constructor.
	 *
	 * @param array|Arguments $args query arguments
	 * @param null|CacheConfig $cacheId
	 */
	function __construct( $args, $cache = null ) {

		// bring octavius query to public (for loop etc)
		self::$instance = $this;

		$this->data = null;
		$this->dataSource = static::DATA_SOURCE_NONE;
		$this->queryServerRequest = new QueryServerRequest($args);
		$this->cache = null != $cache ? new QueryCache(
			$this->queryServerRequest,
			Plugin::instance()->queries,
			$cache
		) : null;
	}

	/**
	 * get received data
	 *
	 * @return array
	 * @throws QueryException
	 */
	function get_data() {

		if(null != $this->data){
			return $this->data;
		}

		if($this->cache instanceof QueryCache){
			$cached = $this->cache->get();
			if(null !== $cached){
				if($this->cache->getDataSource() == QueryCache::DATA_SOURCE_DATABASE){
					$this->dataSource = static::DATA_SOURCE_CACHE;
				} else {
					$this->dataSource = static::DATA_SOURCE_SERVER_WITH_CACHE_UPDATE;
				}

				$this->data = $cached;
				return $this->data;
			}
		}

		$this->data = $this->queryServerRequest->get_data();
		$this->dataSource = static::DATA_SOURCE_SERVER;
		return $this->data;
	}

	/**
	 * which source provided the data
	 * @return string
	 */
	public function getDataSource(){
		return $this->dataSource;
	}

	/**
	 * @param $default_values
	 * @param $time_field_name
	 * @param $from string date
	 * @param $to string date
	 * @param $step string minute, day, hour, month
	 *
	 * @return array
	 * @throws \Exception
	 */
	function get_data_no_gaps( $from, $to, $step, $time_field_name, $default_values ) {
		return $this->queryServerRequest->get_data_no_gaps($from, $to, $step, $time_field_name, $default_values);
	}

	/**
	 * how many microseconds did the request take
	 * false is not requested yet
	 *
	 * @return int|boolean
	 */
	function get_request_time() {
		return $this->dataSource == static::DATA_SOURCE_SERVER ? $this->queryServerRequest->get_request_time()
			: ( $this->cache instanceof QueryCache ? $this->cache->get_request_time() : false );
	}
}

