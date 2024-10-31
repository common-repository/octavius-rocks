<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 09.11.18
 * Time: 09:50
 */

namespace OctaviusRocks\Server;


use OctaviusRocks\Server\Exceptions\AdminSecretException;

class Request {

	/**
	 * @var string
	 */
	private $admin_secret = NULL;

	/**
	 * @var string
	 */
	private $client_secret = NULL;

	/**
	 * @var string
	 */
	private $origin = NULL;

	/**
	 * @return Request
	 */
	public static function builder() {
		return new Request();
	}

	/**
	 * @param null|string $secret
	 *
	 * @return $this
	 */
	public function setAdminSecret( $secret ) {
		$this->admin_secret = $secret;

		return $this;
	}

	/**
	 * @param null|string $secret
	 *
	 * @return $this
	 */
	public function setClientSecret( $secret ) {
		$this->client_secret = $secret;

		return $this;
	}


	/**
	 * @param null|string $origin
	 *
	 * @return $this
	 */
	public function setOrigin( $origin ) {
		$this->origin = $origin;

		return $this;
	}

	/**
	 * @param string $url
	 * @param array $params
	 *
	 * @return Response
	 */
	public function post( $url, $params ) {
		return $this->execute(
			RequestConfiguration::builder( $url )
			                    ->setData( $params )
			                    ->setOrigin( $this->origin )
			                    ->setClientSecret( $this->client_secret )
			                    ->usePOST()
		);
	}

	/**
	 *
	 * @param string $url
	 *
	 * @return Response
	 */
	public function get( $url ) {
		return $this->execute(
			RequestConfiguration::builder( $url )
			                    ->setOrigin( $this->origin )
			                    ->setClientSecret( $this->client_secret )
			                    ->useGET()
		);
	}

	/**
	 * admin post request
	 *
	 * @param $url
	 * @param array $params
	 *
	 * @return Response
	 * @throws AdminSecretException
	 */
	public function postAdmin( $url, $params = array() ) {
		if ( $this->admin_secret == NULL ) {
			throw new AdminSecretException( "No admin secret!" );
		}

		return $this->execute(
			RequestConfiguration::builder( $url )
			                    ->setData( $params )
			                    ->setAdminSecret( $this->admin_secret )
			                    ->setOrigin( $this->origin )
			                    ->usePOST()
		);
	}

	/**
	 * admin patch request
	 *
	 * @param $url
	 * @param array $params
	 *
	 * @return Response
	 * @throws AdminSecretException
	 */
	public function patchAdmin( $url, $params = array() ) {
		if ( $this->admin_secret == NULL ) {
			throw new AdminSecretException( "No admin secret!" );
		}

		return $this->execute(
			RequestConfiguration::builder( $url )
			                    ->setData( $params )
			                    ->setAdminSecret( $this->admin_secret )
			                    ->setOrigin( $this->origin )
			                    ->usePATCH()
		);
	}

	/**
	 * admin delete request
	 *
	 * @param $url
	 * @param array $params
	 *
	 * @return Response
	 * @throws AdminSecretException
	 */
	public function deleteAdmin( $url, $params = array() ) {
		if ( $this->admin_secret == NULL ) {
			throw new AdminSecretException( "No admin secret!" );
		}

		return $this->execute(
			RequestConfiguration::builder( $url )
			                    ->setData( $params )
			                    ->setAdminSecret( $this->admin_secret )
			                    ->setOrigin( $this->origin )
			                    ->useDELETE()
		);
	}

	/**
	 *
	 * @param string $url
	 *
	 * @return Response
	 * @throws AdminSecretException
	 */
	public function getAdmin( $url ) {
		if ( $this->admin_secret == NULL ) {
			throw new AdminSecretException( "No admin secret!" );
		}

		return $this->execute(
			RequestConfiguration::builder( $url )
			                    ->setAdminSecret( $this->admin_secret )
			                    ->setOrigin( $this->origin )
			                    ->useGET()
		);
	}

	/**
	 * uses admin secret if exists else tries normal post request
	 *
	 * @param $url
	 * @param array $params
	 *
	 * @return Response
	 */
	public function postMaybeAdmin( $url, $params = array() ) {

		return $this->execute(
			RequestConfiguration::builder( $url )
			                    ->setData( $params )
			                    ->setClientSecret( $this->client_secret )
			                    ->setAdminSecret( $this->admin_secret )
			                    ->setOrigin( $this->origin )
			                    ->usePOST()
		);
	}

	/**
	 *
	 * @param string $url
	 *
	 * @return Response
	 */
	public function getMaybeAdmin( $url ) {
		return $this->execute(
			RequestConfiguration::builder( $url )
			                    ->setClientSecret( $this->client_secret )
			                    ->setAdminSecret( $this->admin_secret )
			                    ->setOrigin( $this->origin )
			                    ->useGET()
		);
	}

	/**
	 * @param RequestConfiguration $configuration
	 *
	 * @return Response
	 */
	private function execute( RequestConfiguration $configuration ) {

		$curl = curl_init();

		curl_setopt_array( $curl, array(
			CURLOPT_URL            => $configuration->getUrl(),
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_ENCODING       => "",
			CURLOPT_MAXREDIRS      => 10,
			CURLOPT_TIMEOUT        => $configuration->getTimeout(),
			CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST  => $configuration->getMethod(),
			CURLOPT_POSTFIELDS     => $configuration->getPostContentString(),
			CURLOPT_HTTPHEADER     => $configuration->getHeaders(),

		) );
		$server_output = curl_exec( $curl );
		$response_code = curl_getinfo( $curl, CURLINFO_HTTP_CODE );
		$error         = curl_error( $curl );
		curl_close( $curl );


		return Response::parse( $server_output, $response_code );
	}

}