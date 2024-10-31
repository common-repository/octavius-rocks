<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 09.11.18
 * Time: 09:50
 */

namespace OctaviusRocks\Server;

/**
 * Class RequestConfiguration
 *
 * should only be used inside of Request
 *
 * @package OctaviusRocks\Server
 */
class RequestConfiguration {

	const DEFAULT_TIMEOUT = 60;

	/**
	 * @var null|int
	 */
	private static $globalTimeout = null;

	/**
	 * @var int|int
	 */
	private $timeout = null;

	/**
	 * @var string
	 */
	private $url;

	/**
	 * @var
	 */
	private $method;

	/**
	 * RequestConfiguration constructor.
	 *
	 * @param string $url
	 *
	 */
	public function __construct( $url ) {
		$this->url = $url;
		$this->useGET();
	}

	/**
	 * @param int $timeout
	 */
	public static function setGlobalTimeout($timeout){
		self::$globalTimeout = $timeout;
	}

	/**
	 * @param int $timeout
	 */
	public function setTimeout($timeout){
		$this->timeout = $timeout;
	}

	/**
	 * @return int
	 */
	public function getTimeout(){
		return ($this->timeout == null)? (self::$globalTimeout == null)? self::DEFAULT_TIMEOUT : self::$globalTimeout : $this->timeout;
	}

	/**
	 * @return $this
	 */
	public function useGET() {
		$this->method = "GET";

		return $this;
	}

	/**
	 * @return $this
	 */
	public function usePOST() {
		$this->method = "POST";

		return $this;
	}

	/**
	 * @return $this
	 */
	public function useDELETE() {
		$this->method = "DELETE";

		return $this;
	}

	/**
	 * @return $this
	 */
	public function usePATCH() {
		$this->method = "PATCH";

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getMethod() {
		return $this->method;
	}

	/**
	 * @return string
	 */
	public function getUrl() {
		return $this->url;
	}

	/**
	 * @var string|null
	 */
	private $admin_secret = NULL;

	/**
	 * @param string|null $secret
	 *
	 * @return $this
	 */
	public function setAdminSecret( $secret ) {
		$this->admin_secret = $secret;

		return $this;
	}

	private $client_secret = NULL;

	/**
	 * @param string|null $secret
	 *
	 * @return $this
	 */
	public function setClientSecret( $secret ) {
		$this->client_secret = $secret;

		return $this;
	}

	/**
	 * @var
	 */
	private $origin;

	/**
	 * @param string $origin
	 *
	 * @return $this
	 */
	public function setOrigin( $origin ) {
		$this->origin = $origin;

		return $this;
	}

	/**
	 * @var null|array
	 */
	private $request_data = NULL;

	/**
	 * @param $params
	 *
	 * @return RequestConfiguration
	 */
	public function setData( $params ) {
		$this->request_data = $params;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getHeaders() {
		$headers = array(
			"Content-Type: application/json",
			"cache-control: no-cache",
		);
		if ( $this->origin != NULL ) {
			$headers[] = "Origin: {$this->origin}";
		}
		if ( $this->request_data != NULL ) {
			$headers[] = 'Content-Length: ' . strlen( $this->getPostContentString() );
		}
		if ( $this->admin_secret != NULL ) {
			$headers[] = "OctaviusRocks-AdminSecret: " . $this->admin_secret;
		}
		if ( $this->client_secret != NULL ) {
			$headers[] = "OctaviusRocks-ClientSecret: " . $this->client_secret;
		}

		return $headers;
	}

	/**
	 * @return string
	 */
	public function getPostContentString() {
		return ( $this->request_data != NULL ) ? json_encode( $this->request_data ) : "";
	}

	/**
	 * @param $url
	 *
	 * @return \OctaviusRocks\Server\RequestConfiguration
	 */
	public static function builder( $url ) {
		return new RequestConfiguration( $url );
	}

}