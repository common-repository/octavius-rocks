<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 15.11.18
 * Time: 14:35
 */

namespace OctaviusRocks\Server;


use OctaviusRocks\Client\Client;

class ConfigurationsEndpoint {

	/**
	 * @var string
	 */
	private $url;
	/**
	 * @var string
	 */
	private $placeholder;

	/**
	 * @var Request
	 */
	private $request;

	/**
	 * @var ServerConfiguration[]
	 */
	private $configurations = array();

	/**
	 * @param string $url
	 * @param string $api_key_placeholder
	 * @param Request $request
	 *
	 * @return ConfigurationsEndpoint
	 */
	public static function builder( $url, $api_key_placeholder, Request $request ) {
		$endpoint = new ConfigurationsEndpoint();

		return $endpoint
			->setUrl( $url )
			->setPlaceholder( $api_key_placeholder )
			->setRequest( $request );
	}

	/**
	 * @param string $url
	 *
	 * @return $this
	 */
	public function setUrl( $url ) {
		$this->url = $url;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getUrl() {
		return $this->url;
	}

	/**
	 * @param string $placeholder
	 *
	 * @return $this
	 */
	public function setPlaceholder( $placeholder ) {
		$this->placeholder = $placeholder;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getPlaceholder() {
		return $this->placeholder;
	}

	/**
	 * @param Request $request
	 *
	 * @return $this
	 */
	public function setRequest( Request $request ) {
		$this->request = $request;

		return $this;
	}

	/**
	 * @return Request
	 */
	public function getRequest() {

		return $this->request;
	}

	/**
	 * @param $api_key
	 *
	 * @return string
	 */
	public function getServerConfigurationsUrl( $api_key ) {
		return str_replace( $this->placeholder, $api_key, $this->url );
	}

	/**
	 * @param $api_key
	 * @param bool $reload
	 *
	 * @return ServerConfiguration
	 * @throws \Exception
	 */
	public function getServerConfiguration( $api_key, $reload = false ) {
		if ( ! isset( $this->configurations[ $api_key ] ) || $reload ) {
			$response = $this->request->getMaybeAdmin( $this->getServerConfigurationsUrl( $api_key ) );
			if ( $response->isError() ) {
				throw new \Exception( "Could not find server configuration for api key: {$api_key}" );
			}
			$this->configurations[ $api_key ] = ServerConfiguration::parse( $response->getPayload() );
		}

		return $this->configurations[ $api_key ];
	}

	/**
	 * @param string $api_key
	 *
	 * @return Client|null
	 */
	public function getClient( $api_key){
		try{
			$configuration = $this->getServerConfiguration($api_key);
			return new Client($api_key, new ServerConnection($configuration, $this->request));
		} catch ( \Exception $e ) {
			error_log($e->getMessage());
		}
		return null;
	}

}