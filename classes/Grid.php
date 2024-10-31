<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 2019-03-05
 * Time: 19:19
 */

namespace OctaviusRocks;


/**
 * @property Plugin plugin
 */
class Grid {

	/**
	 * Grid constructor.
	 *
	 * @param Plugin $plugin
	 */
	public function __construct(Plugin $plugin) {
		$this->plugin = $plugin;
		add_action('grid_load_classes', array($this, 'load_classes'));
		add_filter('grid_templates_paths', array($this, 'templates'));
		add_filter(Plugin::FILTER_GRID_BOX_SHOW_TAXONOMY, array($this, 'show_taxonomy_in_grid_box'),10 , 2);
	}

	/**
	 * load grid box classes
	 */
	public function load_classes(){
		require_once $this->plugin->path."/grid-boxes/grid_octavius_rocks_top_box.php";
		do_action(Plugin::ACTION_GRID_LOAD_CLASSES);
	}

	public function templates($templates){
		$templates[] = $this->plugin->path."/templates/grid";
		return $templates;
	}

	public function show_taxonomy_in_grid_box($show, $taxonomy){
		return 'post_format' != $taxonomy && $show;
	}
}