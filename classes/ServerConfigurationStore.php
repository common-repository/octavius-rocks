<?php

/**
 * Created by PhpStorm.
 * User: edward
 * Date: 23.10.18
 * Time: 11:14
 */

namespace OctaviusRocks;

use OctaviusRocks\Server\Request;
use OctaviusRocks\Server\ServerConfiguration;
use OctaviusRocks\Server\ServerConnection;

/**
 * Class ServerConfigurationStore
 *
 * @package OctaviusRocks
 */
class ServerConfigurationStore {

	/**
	 * @var ServerConfigurationStore
	 */
	private static $instance = null;

	/**
	 * @return ServerConfigurationStore
	 */
	public static function instance() {
		if ( self::$instance == null ) {
			self::$instance = new ServerConfigurationStore();
		}

		return self::$instance;
	}

	/**
	 * set server configuration from server
	 *
	 * @param ServerConfiguration|null $sc
	 */
	public function persist( $sc ) {
		if ( $sc == null ) {
			delete_option( Plugin::OPTION_SERVER );
			delete_option( Plugin::OPTION_SERVER_PATH );
			delete_option( Plugin::OPTION_API_VERSION );
			delete_option( Plugin::OPTION_API_VERSION_READABLE );
		} else {
			update_option( Plugin::OPTION_SERVER, $sc->getServer() );
			update_option( Plugin::OPTION_SERVER_PATH, $sc->getVersionPath() );
			update_option( Plugin::OPTION_API_VERSION, $sc->getVersion() );
			update_option( Plugin::OPTION_API_VERSION_READABLE, $sc->getVersionName() );
		}
	}

	/**
	 * client configuration which came from server
	 *
	 * @return ServerConfiguration
	 */
	function get() {
		return ServerConfiguration::parse(
			array(
				"server"   => $this->get_server(),
				"path"     => $this->get_server_path(),
				"useHttps" => $this->use_ssl(),
				"version"  => array(
					"id"       => intval( get_option( Plugin::OPTION_API_VERSION, - 1 ) ),
					"readable" => get_option( Plugin::OPTION_API_VERSION_READABLE, '' ),
				),
			)
		);
	}

	/**
	 * @return \OctaviusRocks\Server\ServerConnection|null
	 */
	function connect() {
		if ( ! $this->has_valid_configuration() ) {
			return NULL;
		}
		$domain = parse_url( get_home_url(), PHP_URL_HOST );

		return new ServerConnection(
			$this->get(),
			Request::builder()
			       ->setOrigin( $domain )
			       ->setClientSecret( $this->get_client_secret() )
		);
	}

	/**
	 * check if configuration is valid
	 *
	 * @return bool
	 */
	public function has_valid_configuration() {
		return (
			$this->get_api_key() != ''
			&&
			$this->get_server() != ''
			&&
			$this->get_server_path() != ''
		);
	}

	/**
	 * reset all cached options
	 */
	public function reset_options_cache() {
		$this->api_key            = null;
		$this->server             = null;
		$this->server_path        = null;
		$this->client_secret      = null;
	}

	public function is_api_key_defined_as_constant() {
		return defined( 'OCTAVIUS_ROCKS_API_KEY' )
		       &&
		       !empty(OCTAVIUS_ROCKS_API_KEY);
	}

	/**
	 * @var string
	 */
	private $api_key = null;

	/**
	 * api key for service
	 *
	 * @return string
	 */
	public function get_api_key() {
		if ( defined( 'OCTAVIUS_ROCKS_API_KEY' ) && $this->is_api_key_defined_as_constant() ) {
			return OCTAVIUS_ROCKS_API_KEY;
		}
		if ( $this->api_key == null ) {
			$this->api_key = get_option( Plugin::OPTION_API_KEY, '' );
		}

		return $this->api_key;
	}

	/**
	 * @var string
	 */
	private $server = null;

	/**
	 * get domain for service
	 *
	 * @return mixed
	 */
	public function get_server() {
		if ( defined( 'OCTAVIUS_ROCKS_SERVER' ) ) {
			return OCTAVIUS_ROCKS_SERVER;
		}
		if ( $this->server == null ) {
			$this->server = get_option( Plugin::OPTION_SERVER, '' );
		}

		return $this->server;
	}

	/**
	 * @var string
	 */
	private $server_path = null;

	/**
	 * get server path of version
	 *
	 * @return string
	 */
	public function get_server_path() {
		if ( defined( 'OCTAVIUS_ROCKS_SERVER_PATH' ) ) {
			return OCTAVIUS_ROCKS_SERVER_PATH;
		}
		if ( $this->server_path == null ) {
			$this->server_path = get_option( Plugin::OPTION_SERVER_PATH, '' );
		}

		return $this->server_path;
	}

	/**
	 * @var boolean
	 */
	private $is_click_tracking_disabled = NULL;

	/**
	 * @return boolean
	 */
	public function is_click_tracking_disabled() {
		if ( defined( 'OCTAVIUS_ROCKS_TRACK_CLICKS' ) ) {
			return !OCTAVIUS_ROCKS_TRACK_CLICKS;
		}
		if ( $this->is_click_tracking_disabled == NULL ) {
			$this->is_click_tracking_disabled = (
				get_option( Plugin::OPTION_DISABLE_TRACK_CLICKS, false ) === "disabled"
			);
		}

		return $this->is_click_tracking_disabled;
	}

	/**
	 * @var boolean
	 */
	private $is_rendered_tracking_disabled = NULL;

	/**
	 * @return boolean
	 */
	public function is_rendered_tracking_disabled() {
		if ( defined( 'OCTAVIUS_ROCKS_TRACK_RENDERED' ) ) {
			return !OCTAVIUS_ROCKS_TRACK_RENDERED;
		}
		if ( $this->is_rendered_tracking_disabled == NULL ) {
			$this->is_rendered_tracking_disabled = (
				get_option( Plugin::OPTION_DISABLE_TRACK_RENDERED, false ) === "disabled"
			);
		}

		return $this->is_rendered_tracking_disabled;
	}

	/**
	 * @var boolean
	 */
	private $is_pixel_tracking_disabled = NULL;

	/**
	 * @return boolean
	 */
	public function is_pixel_tracking_disabled() {
		if ( defined( 'OCTAVIUS_ROCKS_TRACK_PIXEL' ) ) {
			return !OCTAVIUS_ROCKS_TRACK_PIXEL;
		}
		if ( $this->is_pixel_tracking_disabled == NULL ) {
			$this->is_pixel_tracking_disabled = (
				get_option( Plugin::OPTION_DISABLE_TRACK_PIXEL, false ) === "disabled"
			);
		}

		return $this->is_pixel_tracking_disabled;
	}

	/**
	 * @var string
	 */
	private $client_secret = null;

	/**
	 * client secret for service
	 *
	 * @return string
	 */
	public function get_client_secret() {
		if ( defined( 'OCTAVIUS_ROCKS_CLIENT_SECRET' ) ) {
			return OCTAVIUS_ROCKS_CLIENT_SECRET;
		}
		if ( $this->client_secret == null ) {
			$this->client_secret = get_option( Plugin::OPTION_CLIENT_SECRET, '' );
		}

		return $this->client_secret;
	}

	/**
	 * for debugging only!
	 *
	 * @return bool
	 */
	public function use_ssl() {
		return (
			! defined( 'OCTAVIUS_ROCKS_USE_SSL' )
			||
			OCTAVIUS_ROCKS_USE_SSL == true
		);
	}


}
