<?php


namespace OctaviusRocks;

use OctaviusRocks\Database\Queries;
use OctaviusRocks\Model\CacheConfig;
use OctaviusRocks\Model\QueryCacheRequest;
use OctaviusRocks\Model\QueryCacheResponse;

class QueryCache {

	const DATA_SOURCE_NONE = "";
	const DATA_SOURCE_DATABASE = "database";
	const DATA_SOURCE_SERVER = "server";
	/**
	 * @var QueryServerRequest
	 */
	private $query;
	/**
	 * @var CacheConfig
	 */
	private $config;
	/**
	 * @var string
	 */
	private $source;
	/**
	 * @var Queries
	 */
	private $db;

	function __construct(
		QueryServerRequest $request,
		Queries $db,
		CacheConfig $config
	) {
		$this->query  = $request;
		$this->db = $db;
		$this->config = $config;
		$this->source = static::DATA_SOURCE_NONE;
	}

	/**
	 * @return string
	 */
	public function getDataSource(){
		return $this->source;
	}

	/**
	 * @return bool|int
	 */
	public function get_request_time(){
		return $this->source === static::DATA_SOURCE_SERVER ? $this->query->get_request_time() : 0;
	}

	/**
	 * @return array|null
	 */
	public function get(){

		$cacheId = $this->config->getId();
		$ttl = $this->config->getTTL();
		$updateAfter = $this->config->getUpdateAfter();

		try {
			$args = $this->query->get_args();
		} catch ( OQL\InvalidQueryException $e ) {
			$this->db->updateErrorResponse($cacheId, $e->getMessage());
			return null;
		}

		$request = new QueryCacheRequest( $cacheId, $args, $ttl, $updateAfter );

		$response = $this->db->get($request->id);
		if($response instanceof QueryCacheResponse){

			$this->db->touch($request);

			if(!$response->hasExpired()){
				$this->source = static::DATA_SOURCE_DATABASE;
				return $response->response;
			}
		}

		try {
			$result = $this->query->get_data();
			$this->source = static::DATA_SOURCE_SERVER;
		} catch ( QueryException $e ) {
			$this->db->updateErrorResponse($request->id, $e->getMessage());
			return null;
		}

		if(null === $response){
			$this->db->add($request->id, $request, $result);
		} else {
			$this->db->updateResponse($response->id, $result);
		}

		return $result;
	}

}