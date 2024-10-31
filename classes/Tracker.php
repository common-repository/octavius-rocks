<?php

namespace OctaviusRocks;


class Tracker {

	private $allowed_attributes = array(
		"viewmode",
		"content_type",
		"variant",
		"pagetype",
		"content_id",
		"content_url",
		"parent_id",
		"parent_url",
		"region",
		"grid_container_number",
		"grid_container_name",
		"grid_slot_number",
		"grid_box_number",
		"grid_box_type",
		"list_number",
		"screen_number",
		"breakpoint",
		"referer_domain",
		"referer_path",
		"tag1",
		"tag2",
		"tag3",
		"tag4",
		"tag5",
		"tag6",
		"tag7",
		"tag8",
		"tag9",
		"tag10",
	);

	/**
	 * Tracker constructor.
	 *
	 * @param Plugin $plugin
	 */
	function __construct( $plugin ) {
		$this->plugin = $plugin;

		add_action( Plugin::ACTION_RENDER_TEASER_ATTRIBUTES, array(
			$this,
			'render_octavius_teaser_attributes_string',
		) );

		add_filter( Plugin::FILTER_TEASER_ATTRIBUTES, array(
			$this,
			'get_octavius_teaser_attributes_string',
		) );

		add_action( Plugin::ACTION_RENDER_ATTRIBUTES, array(
			$this,
			'render_octavius_attributes_string',
		) );

		add_filter( Plugin::FILTER_ATTRIBUTES, array( $this, 'get_octavius_attributes_string' ) );
	}


	function render_octavius_attributes_string( $attributes ) {
		echo $this->get_octavius_attributes_string( $attributes );
	}

	function render_octavius_teaser_attributes_string( $viewmode ) {
		echo $this->get_octavius_teaser_attributes_string( $viewmode );
	}

	/**
	 * @param $attributes
	 * Keys for attributes:
	 * viewmode, content_type, pagetype, content_id, region
	 *
	 * @return string
	 */
	function get_octavius_attributes_string( $attributes ) {

		$attribute_string = "";

		foreach ( $attributes as $name => $value ) {
			if ( in_array( $name, $this->allowed_attributes ) ) {
				$attribute_string .= "data-octavius-$name='$value' ";
			}
		}

		return $attribute_string;
	}

	/**
	 * @param $attributes
	 * Keys for attributes:
	 * viewmode, content_type, pagetype, content_id, region
	 *
	 * @return string
	 */
	function get_octavius_teaser_attributes_string( $viewmode ) {
		if ( $viewmode == NULL ) {
			$viewmode = "default";
		}
		$id        = get_the_ID();
		$post_type = get_post_type();

		$attributes = array(
			"viewmode"     => $viewmode,
			"pagetype"     => $this->plugin->page_info->page_type,
			"content_type" => $post_type,
			"content_id"   => $id,
		);

		if(get_post_format() !== false ) $attributes["variant"] = get_post_format();

		return $this->get_octavius_attributes_string( $attributes );
	}



}
