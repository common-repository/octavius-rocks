<?php


namespace OctaviusRocks\Model;


use DateTime;

/**
 * @property string id
 * @property array arguments
 * @property int ttl
 * @property array|null response
 * @property DateTime touched
 * @property DateTime|null fetched
 * @property DateTime|null errored
 * @property int every
 */
class QueryCacheResponse {

	public function __construct( string $id, array $args, int $ttl, int $every, $response, DateTime $touched, $fetched, $errored ) {
		$this->id        = $id;
		$this->arguments = $args;
		$this->ttl       = $ttl;
		$this->every     = $every;
		$this->response  = $response;
		$this->touched   = $touched;
		$this->fetched   = $fetched;
		$this->errored   = $errored;
	}

	public function needsUpdate() {
		return ( time() - $this->fetched->getTimestamp() ) > $this->every;
	}

	public function hasExpired() {
		return $this->ttl > 0
		       &&
		       $this->fetched instanceof DateTime
		       &&
		       ( time() - $this->fetched->getTimestamp() ) > $this->ttl;
	}
}