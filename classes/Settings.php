<?php

namespace OctaviusRocks;


use OctaviusRocks\Server\ServerConfigurationHandler;

/**
 * @property Plugin plugin
 */
class Settings {

	const MENU_SLUG = "settings";

	const NONCE_NAME = "octavius_rocks";
	const NONCE_ACTION = "save_settings";

	/**
	 * Settings constructor.
	 *
	 * @param Plugin $plugin
	 */
	function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
		add_action( 'admin_menu', array( $this, 'admin_init' ) );
		add_action( $this->get_settings_action( "license" ), array(
			$this,
			"render_license_settings",
		) );
		add_action( $this->get_settings_action( "general" ), array(
			$this,
			"render_general_settings",
		) );
		add_action( $this->get_settings_action( "advanced" ), array(
			$this,
			"render_advanced_settings",
		) );
		add_action( $this->get_settings_action( "health" ), array(
			$this,
			"render_health_settings",
		) );
		add_filter( 'plugin_action_links_' . $this->plugin->basename, array(
			$this,
			'add_action_links'
		) );
	}

	/**
	 * for settings links on plugin list site
	 * filter is called in Plugin class
	 *
	 * @param array $links
	 *
	 * @return array
	 */
	function add_action_links( $links ) {
		$settings_url = $this->get_path_with_params();
		$links[]      = "<a href='$settings_url'>" . __( "Settings", Plugin::DOMAIN ) . "</a>";

		return $links;
	}

	/**
	 * @param array $params_to_add
	 *
	 * @return string
	 */
	public function get_path_with_params( $params_to_add = array() ) {
		$url    = admin_url( "admin.php" );
		$params = array( "page" => "octavius-rocks-settings" );

		foreach ( $params_to_add as $key => $param ) {
			$params[ $key ] = $param;
		}

		$url_parts['query'] = http_build_query( $params );

		return add_query_arg( $params, $url );
	}

	/**
	 * admin notice for settings
	 */
	public function admin_notices() {

		$notices = array();

		$config = $this->plugin->config;

		$settings_url = $this->get_path_with_params();

		if ( "" == $config->get_api_key() ) {
			$notices[] = __( 'API-Key is missing.', Plugin::DOMAIN );
		} else {
			if ( ! $config->has_valid_configuration() ) {
				$notices[] = __( 'No valid server configuration. Is your api key valid?', Plugin::DOMAIN );
			}
		}

		if (
			count( $notices ) > 0
			&&
			! ( ! empty( $_GET["page"] ) && $_GET["page"] === 'octavius-rocks' )
		) {
			$notices[] = "<p><a href='$settings_url'>" . __( "Goto configuration.", Plugin::DOMAIN ) . "</a></p>";
		}


		if ( count( $notices ) > 0 ) {

			$notice = "";
			$notice .= "<p>" . implode( "</p><p>", $notices ) . "</p>";

			$this->plugin->admin_notices->enqueue( $notice );
		}

	}

	/**
	 * save setting forms and add submenu page
	 */
	public function admin_init() {


		//---------------------------
		// save form data
		//---------------------------

		$this->save_form();


		//---------------------------------------------------------------------
		// always fetch configuration if not valid and api key is in constants
		//---------------------------------------------------------------------
		$config = $this->plugin->config;
		if (
			! $config->has_valid_configuration()
			&&
			$config->is_api_key_defined_as_constant()
		) {
			ServerConfigurationHandler::fetch_server_configuration();
		}

		// check for admin notices
		$this->admin_notices();
	}

	public function save_form() {
		if ( empty( $_POST ) || !isset($_POST[ static::NONCE_NAME ]) ) {
			return;
		}


		if ( ! wp_verify_nonce( $_POST[ static::NONCE_NAME ], static::NONCE_ACTION ) ) {
			return;
		}

		if ( isset( $_POST[ Plugin::OPTION_API_KEY ] ) ) {

			if ( $_POST[ Plugin::OPTION_API_KEY ] != "" ) {
				update_option( Plugin::OPTION_API_KEY, sanitize_text_field( $_POST[ Plugin::OPTION_API_KEY ] ) );
				ServerConfigurationHandler::fetch_server_configuration();
			} else {
				delete_option( Plugin::OPTION_API_KEY );
				$this->plugin->config->persist( null );
			}
			$this->plugin->config->reset_options_cache();
		}

		if ( isset( $_POST[ Plugin::OPTION_CLIENT_SECRET ] ) ) {
			if ( isset( $_POST[ Plugin::OPTION_CLIENT_SECRET ] ) && "" != $_POST[ Plugin::OPTION_CLIENT_SECRET ] ) {
				update_option( Plugin::OPTION_CLIENT_SECRET, sanitize_text_field( $_POST[ Plugin::OPTION_CLIENT_SECRET ] ) );
			} else {
				delete_option( Plugin::OPTION_CLIENT_SECRET );
			}
			$this->plugin->config->reset_options_cache();
		}

		if ( isset( $_POST[ Plugin::OPTION_DISABLE_TRACK_CLICKS . "_set" ] ) ) {
			if ( isset( $_POST[ Plugin::OPTION_DISABLE_TRACK_CLICKS ] ) && "" != $_POST[ Plugin::OPTION_DISABLE_TRACK_CLICKS ] ) {
				update_option( Plugin::OPTION_DISABLE_TRACK_CLICKS, sanitize_text_field( $_POST[ Plugin::OPTION_DISABLE_TRACK_CLICKS ] ) );
			} else {
				delete_option( Plugin::OPTION_DISABLE_TRACK_CLICKS );
			}
		}

		if ( isset( $_POST[ Plugin::OPTION_DISABLE_TRACK_RENDERED . "_set" ] ) ) {
			if ( isset( $_POST[ Plugin::OPTION_DISABLE_TRACK_RENDERED ] ) && "" != $_POST[ Plugin::OPTION_DISABLE_TRACK_RENDERED ] ) {
				update_option( Plugin::OPTION_DISABLE_TRACK_RENDERED, sanitize_text_field( $_POST[ Plugin::OPTION_DISABLE_TRACK_RENDERED ] ) );
			} else {
				delete_option( Plugin::OPTION_DISABLE_TRACK_RENDERED );
			}
		}

		if ( isset( $_POST[ Plugin::OPTION_DISABLE_TRACK_PIXEL . "_set" ] ) ) {
			if ( isset( $_POST[ Plugin::OPTION_DISABLE_TRACK_PIXEL ] ) && "" != $_POST[ Plugin::OPTION_DISABLE_TRACK_PIXEL ] ) {
				update_option( Plugin::OPTION_DISABLE_TRACK_PIXEL, sanitize_text_field( $_POST[ Plugin::OPTION_DISABLE_TRACK_PIXEL ] ) );
			} else {
				delete_option( Plugin::OPTION_DISABLE_TRACK_PIXEL );
			}
		}

	}

	public function nonce_field(){
		wp_nonce_field( static::NONCE_ACTION, static::NONCE_NAME );
	}

	/**
	 * @param string $type
	 *
	 * @return string
	 */
	public function get_settings_action( $type ) {
		return str_replace( "{{type}}", $type, Plugin::ACTION_RENDER_SETTINGS );
	}

	// ----------------------------
	// render
	// ----------------------------

	/**
	 *  renders settings page for octavius
	 */
	public function render_octavius_settings() {
		$this->plugin->assets->enqueueAdminScript();
		$current = ( isset( $_GET["tab"] ) ) ? $_GET["tab"] : "license";
		$tabs    = array(
			'license'  => __( 'License', Plugin::DOMAIN ),
			'advanced' => __( 'Advanced', Plugin::DOMAIN ),
			'health'   => __( 'Healthcheck', Plugin::DOMAIN ),
		);
		//TODO hook to get settings tabs from other plugins
		require $this->plugin->path_parts . "/octavius-settings-tabs.php";
		do_action( $this->get_settings_action( $current ), $this );

	}

	/**
	 * render general settings page
	 */
	public function render_general_settings() {
		require $this->plugin->path_parts . "/octavius-settings-general.php";
	}

	/*
	 * render license settings page
	 */
	public function render_license_settings() {
		$this->plugin->schedule->manage();
		require $this->plugin->path_parts . "/octavius-settings-license.php";
	}

	/**
	 * render advanced settings page
	 */
	public function render_advanced_settings() {
		$this->plugin->schedule->manage();
		require $this->plugin->path_parts . "/octavius-settings-advanced.php";
	}

	/**
	 * check system health
	 */
	public function render_health_settings() {
		require $this->plugin->path_parts . "/octavius-settings-health.php";
	}
}
