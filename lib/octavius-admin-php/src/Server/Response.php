<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 13.11.18
 * Time: 15:47
 */

namespace OctaviusRocks\Server;

class Response {

	/**
	 * @var bool
	 */
	private $error;

	/**
	 * @var null|array
	 */
	private $payload;

	/**
	 * @var int
	 */
	private $response_code;

	/**
	 * Response constructor.
	 *
	 * @param bool $error
	 * @param null|array|object $payload
	 */
	public function __construct( $error, $payload = NULL, $response_code = 200 ) {
		$this->error         = $error;
		$this->payload       = $payload;
		$this->response_code = $response_code;
	}

	/**
	 * @return bool
	 */
	public function isError() {
		return $this->response_code != 200 || $this->error;
	}

	/**
	 * @return bool
	 */
	public function isSuccess() {
		return ! $this->isError();
	}

	/**
	 * @return int
	 */
	public function getResponseCode() {
		return $this->response_code;
	}

	/**
	 * get payload object or value of key
	 *
	 * @param null|string $key
	 *
	 * @return string|int|array|null
	 */
	public function getPayload( $key = NULL ) {
		if ( is_string( $key ) ) {
			return ( isset( $this->payload[ $key ] ) ) ? $this->payload[ $key ] : NULL;
		}

		return $this->payload;
	}

	static function parse( $server_response, $response_code = 200 ) {

		// true is a valid response
		if ( $server_response === "true" || $server_response === true ) {
			return new Response( false );
		}

		// else we want to parse json
		$assoc = json_decode( $server_response, true );
		if ( ! is_array( $assoc ) ) {
			return new Response( true );
		}
		$payload = array();
		foreach ( $assoc as $key => $value ) {
			if ( $key == "error" ) {
				continue;
			}
			$payload[ $key ] = $value;
		}

		return new Response( isset( $assoc["error"] ) && $assoc["error"] === true, $payload, $response_code );
	}
}