<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 02.05.18
 * Time: 09:25
 */

namespace OctaviusRocks;

use OctaviusRocks\OQL\Arguments;
use OctaviusRocks\OQL\Condition;
use OctaviusRocks\OQL\ConditionSet;
use OctaviusRocks\OQL\Field;

/**
 * @property Plugin plugin
 */
class MetaBox {

	const BOX_ID = "octavius-rocks-meta-box";

	/**
	 * MetaBox constructor.
	 *
	 * @param Plugin $plugin
	 */
	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
	}

	function add_meta_box() {
	    if(!$this->plugin->gutenberg->isGutenberg()){
            $this->plugin->assets->enqueuePostMetaBoxJS();
            add_meta_box(
                self::BOX_ID,
                __( 'Octavius Rocks', Plugin::DOMAIN ),
                array( $this, 'render' )
            );
        }
	}


	function render( $post ) {
		echo "<p>Loading...</p>";
	}

}