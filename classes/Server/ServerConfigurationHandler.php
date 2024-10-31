<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 23.10.18
 * Time: 12:32
 */

namespace OctaviusRocks\Server;

use OctaviusRocks\Plugin;
use OctaviusRocks\ServerConfigurationStore;

class ServerConfigurationHandler{

	/**
	 * update the server configuration store
	 *
	 * @return boolean
	 *
	 */
	public static function fetch_server_configuration() {
		$datetime = null;
		try{
			$datetime = new \DateTime();
		} catch (\Exception $e){
			return false;
		}
		$tz = get_option('timezone_string');
		$tz = (!empty($tz))? $tz: "UTC";
		$datetime->setTimezone(new \DateTimeZone($tz));
		$run = $datetime->format( "Y.m.d H:i:s" );
		update_option( Plugin::OPTION_FETCH_CONFIG_LAST_RUN, $run );

		$store   = ServerConfigurationStore::instance();
		$api_key = $store->get_api_key();

		// escape if no api key
		if ( empty( $api_key ) ) {
			return false;
		}

		$url = (defined('OCTAVIUS_ROCKS_CLIENT_CONFIGURATIONS_URL'))?
			OCTAVIUS_ROCKS_CLIENT_CONFIGURATIONS_URL
			:
			"https://service.octavius.rocks/config/%api_key%";

		$placeholder = (defined('OCTAVIUS_ROCKS_ClIENT_CONFIGURATIONS_PLACEHOLDER'))?
			OCTAVIUS_ROCKS_ClIENT_CONFIGURATIONS_PLACEHOLDER :	"%api_key%";

		$config = apply_filters(Plugin::FILTER_CONFIGURATION_ENDPOINT, (object) array(
			"url" => $url,
			"placeholder" => $placeholder
		));

		$domain = parse_url(get_home_url(), PHP_URL_HOST);

		$endpoint = ConfigurationsEndpoint::builder(
			$config->url,
			$config->placeholder,
			Request::builder()
			       ->setClientSecret( $store->get_client_secret() )
			       ->setOrigin($domain)
		);

		try {

			$serverConfiguration = $endpoint->getServerConfiguration( $api_key );
			update_option( Plugin::OPTION_FETCH_CONFIG_RESULT, $serverConfiguration->get() );
			$store->persist( $serverConfiguration );
			update_option( Plugin::OPTION_FETCH_CONFIG_LAST_RUN_SUCCESS, $run );
			return true;
		} catch ( \Exception $e ) {
			error_log( $e->getMessage() );
		}
		return false;
	}

	/**
	 * was last run successful?
	 * @return bool
	 */
	public static function was_last_fetch_successful(){
		$lastRun = get_option(Plugin::OPTION_FETCH_CONFIG_LAST_RUN, '');
		$lastSuccessfulRun = get_option(Plugin::OPTION_FETCH_CONFIG_LAST_RUN_SUCCESS, '');

		return $lastRun == $lastSuccessfulRun;
	}

}
