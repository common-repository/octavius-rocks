<?php

namespace OctaviusRocks;

use OctaviusRocks\Compat\WPML;
use OctaviusRocks\Components\Component;

class Compat extends Component {
	public function onCreate() {
		parent::onCreate();

		new WPML($this->plugin);
	}
}