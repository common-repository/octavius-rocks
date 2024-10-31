<?php

namespace OctaviusRocks\Widgets;

use OctaviusRocks\Assets;
use OctaviusRocks\Plugin;

/**
 * @property Plugin plugin
 */
class Statistics {

	const WIDGET_ID = "statistics-rocks";

	function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	/**
	 * Load the widget code
	 */
	public function widget() {
		?>
		<div id="oc-widget-pageviews">
			<div class="ocdb-spinner">
				<div></div>
				<div></div>
			</div>
		</div>
		<?php
	}
}