<?php

use OctaviusRocks\OQL\Arguments;
use OctaviusRocks\OQL\Condition;
use OctaviusRocks\OQL\ConditionSet;
use OctaviusRocks\OQL\Field;
use OctaviusRocks\OQL\OrderBy;
use OctaviusRocks\QueryException;
use OctaviusRocks\Widgets\Utils;

/**
 * Created by PhpStorm.
 * User: edward
 * Date: 2019-03-05
 * Time: 19:21
 *
 * @property \OctaviusRocks\Widgets\Utils utils
 */
class grid_octavius_rocks_top_box extends grid_list_box {

	public function __construct() {
		parent::__construct();

		$this->utils = new Utils();
		$this->content = $this->utils->getContent();
	}

	public function build( $editmode ) {

		if ( $editmode ) {
			return $this->content;
		}

		$utils = new Utils();
		$utils->setContent($this->content);

		$items = $utils->fetch();

		$content        = $this->content;

		$count  = isset( $content->count ) ? intval( $content->count ) : Utils::DEFAULT_COUNT;
		$offset = isset( $content->offset ) ? intval( $content->offset ) : Utils::DEFAULT_OFFSET;

		$content->viewmode = isset($this->content->viewmode) ? $this->content->viewmode : "";

		$content->items = ( $offset > 0 ) ? array_slice( $items, $offset, $count ) : $items;

		$content->post_ids = array_map(
			function ( $item ) {
				return $item[ Field::CONTENT_ID ];
			},
			$content->items
		);
		$content->hits     = array_map(
			function ( $item ) {
				return $item[ Field::HITS ];
			},
			$content->items
		);

		return $content;
	}

	/**
	 * Sets box type
	 *
	 * @return string
	 */
	public function type() {
		return 'octavius_rocks_top';
	}

	public function contentStructure() {
		$cs = parent::contentStructure();
		return array_merge($cs, $this->utils->contentStructure());
	}

	/**
	 * @deprecated use Utils class
	 * @return array of items with shape  array( "key" => "viewmode", "text" =>
	 *     "My viewmode" )
	 */
	private function getViewmodes() {
		return $this->utils->getViewmodes();
	}

	/**
	 * @deprecated use Utils class
	 * @return array
	 */
	private function showTaxonomy($taxonomy){
		return $this->utils->showTaxonomy($taxonomy);
	}

	/**
	 * @deprecated use Utils class
	 * @return array
	 */
	public function getPostTypes(){
		return $this->utils->getPostTypes();
	}

	/**
	 * @deprecated use Utils class
	 * @return array
	 */
	public function getPostTypeSlugs(){
		return $this->utils->getPostTypeSlugs();
	}

	/**
	 * @param bool $withEmpty
	 *
	 * @return array
	 * @deprecated use Utils class
	 */
	public function getTaxonomies($withEmpty = false){
		return $this->utils->getTaxonomies($withEmpty);
	}

	/**
	 * @deprecated use Utils class
	 * @return string
	 */
	public function getTaxonomy($key){
		return $this->utils->getTaxonomy($key);
	}

	/**
	 * @param $taxonomy
	 *
	 * @return string
	 * @deprecated use Utils class
	 */
	public function getTaxonomyKey($taxonomy){
		return $this->utils->getTaxonomyKey($taxonomy);
	}


}