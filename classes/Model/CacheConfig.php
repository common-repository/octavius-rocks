<?php


namespace OctaviusRocks\Model;

class CacheConfig {
	/**
	 * @var int
	 */
	private $ttl;

	/**
	 * @var string
	 */
	private $id;
	/**
	 * @var int
	 */
	private $updateAfterSeconds;

	/**
	 * CacheConfig constructor.
	 *
	 * @param string $id
	 */
	private function __construct( string $id ) {
		$this->id = $id;
		$this->setTTL(0)->setUpdateAfter( HOUR_IN_SECONDS );
	}

	/**
	 * @param string $id
	 *
	 * @return CacheConfig
	 */
	public static function build(string $id){
		return new self($id);
	}

	/**
	 * set time to live
	 * @param int $ttl
	 *
	 * @return $this
	 */
	public function setTTL(int $ttl){
		$this->ttl = $ttl;
		return $this;
	}

	/**
	 * set seconds in which every fetch update should happen
	 *
	 * @param int $seconds
	 *
	 * @return $this
	 */
	public function setUpdateAfter(int $seconds){
		$this->updateAfterSeconds = $seconds;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getId(){
		return $this->id;
	}

	/**
	 * @return int
	 */
	public function getTTL(){
		return $this->ttl;
	}

	/**
	 * update cache every x seconds
	 * @return int
	 */
	public function getUpdateAfter(){
		return $this->updateAfterSeconds;
	}
}
