<?php


namespace OctaviusRocks\Model;


/**
 * @property string id
 * @property array args
 * @property int ttl
 * @property int $updateAfter
 */
class QueryCacheRequest {

	/**
	 * QueryCacheRequest constructor.
	 *
	 * @param string $id
	 * @param array $args oql
	 * @param int $ttl in seconds
	 * @param int $updateAfter in seconds
	 */
	public function __construct(string $id, array $args, int $ttl = 0, int $updateAfter = 0) {
		$this->id          = $id;
		$this->args        = $args;
		$this->ttl         = $ttl;
		$this->updateAfter = $updateAfter;
	}

}