<?php

namespace OctaviusRocks\Compat;

use OctaviusRocks\Components\Component;
use OctaviusRocks\Plugin;
use WP_Post;
use WP_Post_Type;
use WP_Term;

class WPML extends Component {
	public function onCreate() {
		parent::onCreate();
		add_action( 'plugins_loaded', [ $this, 'plugins_loaded' ] );
	}

	public function plugins_loaded() {
		add_filter( Plugin::FILTER_API_RESPONSE_GET_CONTENTS_ITEM, [ $this, 'get_contents_item' ], 10, 3 );
		add_filter( Plugin::FILTER_POSTS_TABLE_PAGE_VIEWS, [ $this, 'posts_table_page_views' ], 10, 2 );
		add_filter( Plugin::FILTER_POSTS_TABLE_POSTS_GROUP, [$this, 'posts_table_posts_group'], 10, 2 );

		add_action( Plugin::ACTION_POSTS_TABLE_PAGE_VIEWS_COL , [$this, 'page_views_col']);
	}

	/**
	 * @param string|int|null $identifier
	 * @param WP_Post|WP_Term|WP_Post_Type|string|null $object
	 *
	 * @return array
	 */
	public function get_contents_item( $item, $identifier, $object ) {
		if ( ! function_exists( 'wpml_get_language_information' ) ) {
			return $item;
		}
		if ( $object instanceof WP_Post ) {
			$info          = wpml_get_language_information( null, $object->ID );
			$item["title"] = $item["title"] . " | " . $info["display_name"];
		}

		return $item;

	}

	public function posts_table_page_views($pageviews, $post_id) {

		if(!function_exists('wpml_get_current_language')) return $pageviews;

		$languages = apply_filters( 'wpml_active_languages', null, []);
		$currentLanguage = wpml_get_current_language();
		foreach ($languages as $language => $noNeedFor){
			if($language != $currentLanguage){
				$translationPostId = apply_filters( 'wpml_object_id', $post_id, get_post_type($post_id), FALSE, $language );
				$pageviews = $pageviews + $this->plugin->pageviews->getPostPageviews($translationPostId);
			}
		}

		return $pageviews;
	}

	public function posts_table_posts_group($postIds, $post_id) {
		if(!function_exists('wpml_get_current_language')) return $postIds;

		$languages = apply_filters( 'wpml_active_languages', null, []);
		foreach ($languages as $language => $noNeedFor){
			$translationPostId = apply_filters( 'wpml_object_id', $post_id, get_post_type($post_id), FALSE, $language );
			if(in_array($translationPostId, $postIds)) continue;
			$postIds[] = $translationPostId;
		}

		return $postIds;
	}

	public function page_views_col($post_id){
		if(!function_exists('wpml_get_current_language')) return;

		echo "<div><small>";
		_e("Total over all languages", Plugin::DOMAIN);
		echo "</small></div>";
	}
}