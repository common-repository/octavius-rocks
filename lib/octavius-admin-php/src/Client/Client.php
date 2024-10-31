<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 09.11.18
 * Time: 09:52
 */

namespace OctaviusRocks\Client;

use OctaviusRocks\OQL\Arguments;
use OctaviusRocks\OQL\InvalidQueryException;
use OctaviusRocks\Server\Response;
use OctaviusRocks\Server\ServerConnection;

class Client {

	/**
	 * @var string
	 */
	private $api_key;

	/**
	 * @var ServerConnection
	 */
	private $connection;

	/**
	 * Client constructor.
	 *
	 * @param string $api_key
	 * @param ServerConnection $serverConnection
	 */
	public function __construct( string $api_key, ServerConnection $serverConnection = null ) {
		$this->api_key    = $api_key;
		$this->connection = $serverConnection;
	}

	/**
	 * @return string
	 */
	public function getApiKey() {
		return $this->api_key;
	}

	/**
	 * @return ServerConnection
	 */
	public function getServerConnection(){
		return $this->connection;
	}

	/**
	 * @return null|array
	 */
	public function getProps() {
		$response = $this->connection->getClient( $this->api_key );

		return ( $response->isError() ) ? NULL : $response->getPayload();
	}

	/**
	 *
	 * @param ClientProperties $params
	 *
	 * @return bool
	 */
	public function setProps( ClientProperties $params ) {
		return $this->connection->setClientProps( $this->api_key, $params->get() )
		                        ->isSuccess();
	}

	/**
	 * @param Arguments|array $oql
	 *
	 * @return Response
	 */
	public function query( $oql ) {
		try {
			$args = $oql instanceof Arguments ? $oql->get() : $oql;
			return $this->connection->query( $this->getApiKey(), $args );
		} catch ( InvalidQueryException $e ) {
			return new Response( true, $e );
		}
	}

	/**
	 * @return string|null
	 */
	public function generateSecret(){
		return $this->connection->generateClientSecret($this->api_key);
	}

}