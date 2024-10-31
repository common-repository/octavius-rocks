<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 13.11.18
 * Time: 15:33
 */

namespace OctaviusRocks\Server;


use OctaviusRocks\Client\ClientProperties;
use OctaviusRocks\Server\Exceptions\AdminSecretException;

class ServerConnection {

	/**
	 * @var ServerConfiguration
	 */
	private $configuration;
	/**
	 * @var Request
	 */
	private $request;

	/**
	 * ServerConnection constructor.
	 *
	 * @param ServerConfiguration $configuration
	 * @param Request $request
	 */
	public function __construct(ServerConfiguration $configuration, Request $request) {
		$this->configuration = $configuration;
		$this->request = $request;
	}

	/**
	 * @return ServerConfiguration
	 */
	public function getConfiguration(){
		return $this->configuration;
	}

	/**
	 * @param ClientProperties $props
	 *
	 * @return string|null
	 */
	function createClient(ClientProperties $props){
		try {
			$response = $this->request->postAdmin(
				$this->configuration->getUrl( "admin/clients" ),
				$props->get()
			);
			return ($response->isSuccess())? $response->getPayload("id"): null;
		} catch ( AdminSecretException $e ) {
			return null;
		}
	}

	/**
	 * @param $api_key
	 *
	 * @return bool
	 */
	function deleteClient($api_key){
		try{
			$response = $this->request->deleteAdmin(
				$this->configuration->getUrl("admin/clients/$api_key")
			);
			return $response->isSuccess();
		} catch (AdminSecretException $e){
			return false;
		}
	}

	/**
	 * @param string $api_key
	 *
	 * @return Response
	 */
	function getClient($api_key){
		return $this->request->getMaybeAdmin(
			$this->configuration->getUrl(
				"admin/clients/{$api_key}"
			)
		);
	}

	/**
	 * @param string $api_key
	 * @param array $params
	 *
	 * @return Response
	 */
	function setClientProps($api_key, $params){
		try{
			return $this->request->patchAdmin(
				$this->configuration->getUrl(
					"admin/clients/{$api_key}"
				),
				$params
			);
		} catch (\Exception $e){
			return new Response(true);
		}
	}

	/**
	 * oql query
	 * @param $api_key
	 * @param array $args
	 *
	 * @return Response
	 */
	function query($api_key, $args){
		return $this->request->postMaybeAdmin(
			$this->configuration->getUrl("get/{$api_key}/query"),
			$args
		);
	}

	/**
	 * @param $api_key
	 *
	 * @return null|string
	 */
	function generateClientSecret($api_key){
		try{
			$response = $this->request->postAdmin(
				$this->configuration->getUrl("admin/clients/$api_key/secret")
			);
			if($response->isError()) {
				error_log("Could not generate new client secret");
				return null;
			}
			return $response->getPayload('secret');
		} catch (\Exception $e){
			error_log($e->getMessage());
			return null;
		}

	}

	/**
	 * see if we can get some communication
	 * @return bool
	 */
	public function checkConnection(){
		$response = $this->request->get(
			$this->configuration->getUrl("_info")
		);
		return $response->getResponseCode() === 200;
	}

	/**
	 * @param string $api_key
	 *
	 * @return bool
	 */
	public function checkClientSecret($api_key){
		try{
			$response = $this->request->get(
				$this->configuration->getUrl("admin/clients/$api_key/secret/check")
			);
			return $response->isSuccess();
		} catch (\Exception $e){
			error_log($e->getMessage());
		}
		return false;
	}

}