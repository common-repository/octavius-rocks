<?php

namespace OctaviusRocks\Widgets;

use OctaviusRocks\Assets;
use OctaviusRocks\Plugin;

/**
 * @property Plugin plugin
 */
class LiveUsers {

	const WIDGET_ID = "live-users-rocks";

	function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	/**
	 * Load the widget code
	 */
	public function widget() {
		?>
		<div id="octavius-rocks-realtime-widget">
			<div class="ocdb-spinner">
				<div></div>
				<div></div>
			</div>
		</div>
		<?php
	}

	public function config(){
		$this->trySaveConfig();
		?>
		<input type="hidden" name="octavius_rocks_realtime_config" value="save" />
		<div id="octavius-rocks-realtime-config"></div>
		<?php
	}

	function getConfig(){
		return get_option("octavius_rocks_realtime_breakpoints", array());
	}
	private function saveConfig($config){
		update_option("octavius_rocks_realtime_breakpoints", $config);
	}
	private function trySaveConfig(){

		if(
			isset($_POST["octavius_rocks_realtime_config"])
			&&
			$_POST["octavius_rocks_realtime_config"] == "save"
		){
			// delete first and have a look for new configs
			$this->saveConfig(array());
		}

		if(
			isset($_POST["octavius_rocks_realtime_breakpoints"])
			&&
		    is_array($_POST["octavius_rocks_realtime_breakpoints"])
		){
			$bps = array();
			foreach ($_POST["octavius_rocks_realtime_breakpoints"] as $obj){
				$theKey = sanitize_text_field($obj["key"]);
				$minWidth = intval($obj["minWidth"]);
				$maxWidth = intval($obj["maxWidth"]);
				if(empty($theKey) || ($maxWidth <= 0 && $minWidth <= 0)) continue;

				$bps[] = array(
					"key" => $theKey,
					"minWidth" => ($minWidth > 0)? $minWidth: "",
					"maxWidth" => ($maxWidth > 0)? $maxWidth: "",
				);
			}
			usort($bps, function($a,$b){
				return intval($b["minWidth"]) - intval($a["minWidth"]);
			});
			$this->saveConfig($bps);
		}
	}
}