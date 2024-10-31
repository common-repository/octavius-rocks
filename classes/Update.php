<?php

namespace OctaviusRocks;

/**
 * Class Update
 *
 * @property Plugin plugin
 * @package OctaviusRocks
 */
class Update {

	// increment this for an update
	const DATA_VERSION = 5;

	// key for data version option
	static function OPTION_DATA_VERSION(){
		return Plugin::DOMAIN."_data_version";
	}

	/**
	 * Update constructor.
	 *
	 * @param Plugin $plugin
	 */
	function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
		add_action( 'admin_init', array( $this, "check_updates" ) );
	}

	function set_version($version){
		return update_option( self::OPTION_DATA_VERSION(), $version );
	}

	function get_version(){
		return get_option(self::OPTION_DATA_VERSION(), self::DATA_VERSION);
	}

	/**
	 * check for updates
	 */
	function check_updates() {
		$current_version = get_option( self::OPTION_DATA_VERSION(), 0 );

		for ( $i = $current_version + 1; $i <= self::DATA_VERSION; $i ++ ) {
			$method = "update_{$i}";
			if ( method_exists( $this, $method ) ) {
				$this->$method();
				$this->set_version( $i );
			}
		}

	}

	/**
	 * update old keys to new namespace
	 * clean code! clean database!
	 */
	function update_1() {
		update_site_option(Plugin::OPTION_API_KEY, get_option("ph_octavius_api_key", ''));
		update_site_option(Plugin::OPTION_SERVER, get_option("ph_octavius_server", ''));
	}

	/**
	 * split up configuration
	 */
	function update_2(){
		$url = get_site_option(Plugin::OPTION_SERVER, '');
//		$parsed = parse_url($url);

	}

	function update_3(){
		// comes with version 3 so create tables
		$this->plugin->pageviews->createTables();
		// start new schedule
		$this->plugin->schedule->manage();
	}

	function update_4(){
		$this->plugin->queries->createTables();
	}

	function update_5(){
		$this->plugin->taxonomyTermViews->createTables();
	}

}
