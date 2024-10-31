<?php


namespace OctaviusRocks;


use OctaviusRocks\BlockX\MostReadBlock;

/**
 * @property Plugin plugin
 */
class BlockX {

	/**
	 * BlockX constructor.
	 */
	public function __construct(Plugin $plugin) {
		$this->plugin = $plugin;
		add_action('blockx_collect', [$this, 'collect']);
		add_filter('blockx_add_templates_paths', [$this, 'add_templates_paths']);
	}

	/**
	 * @param \Palasthotel\WordPress\BlockX\Gutenberg $gutenberg
	 */
	function collect(\Palasthotel\WordPress\BlockX\Gutenberg $gutenberg){
		$gutenberg->addBlockType(new MostReadBlock());
	}

	/**
	 * @param string[] $paths
	 *
	 * @return string[]
	 */
	function add_templates_paths( array $paths ){
		$paths[] = $this->plugin->path."/templates/";
		return $paths;
	}
}