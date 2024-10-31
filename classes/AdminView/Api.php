<?php

namespace OctaviusRocks\AdminView;

use DateTimeZone;
use OctaviusRocks\OQL\Arguments;
use OctaviusRocks\Plugin;

/**
 * @property Plugin plugin
 */
class Api {

	/**
	 * @var Api
	 */
	private static $instance;

	/**
	 * @return Api
	 */
	static function get_instance() {
		return self::$instance;
	}

	/**
	 * OverviewAPI constructor.
	 *
	 * @param $plugin Plugin
	 */
	function __construct( $plugin ) {

		// bring octavius query to public (for loop etc)
		self::$instance = $this;

		$this->plugin = $plugin;

		add_action( 'wp_ajax_oc_get_contents', array( $this, 'get_contents' ) );
	}

	function get_contents() {
		$post = $this->getParams();

		if ( isset( $post["content_ids"] ) && is_array($post["content_ids"]) ) {
			$datetime = new \DateTime();
			if(function_exists("wp_timezone")){
				$datetime->setTimezone(wp_timezone());
			} else {
				$tz = get_option('timezone_string');
				if(!empty($tz)){
					$datetime->setTimezone(new DateTimeZone($tz));
				}
			}

			$date = $datetime->format("Y-m-d H:i:s");

			$results = array_map(function($id) use ( $date ) {
				if( strpos($id, "author/") === 0){
					$authorId = intval(str_replace("author/", "", $id));
					$user = get_user_by("ID", $authorId);
					if($user instanceof \WP_User){
						return  apply_filters(
							Plugin::FILTER_API_RESPONSE_GET_CONTENTS_ITEM,
							[
								"url" => get_author_posts_url($authorId),
								"title" => $user->display_name,
								"ID" => $id,
								"found" => $date,
							],
							$authorId,
							$user
						);
					}
				} else if( strpos($id,"taxonomy_term/") === 0) {
					$taxonomyTermId = intval(str_replace("taxonomy_term/", "", $id));
					$term = get_term_by("term_taxonomy_id", $taxonomyTermId);
					if($term instanceof \WP_Term){
						return apply_filters(
							Plugin::FILTER_API_RESPONSE_GET_CONTENTS_ITEM,
							[
								"url" => get_term_link($term->term_id, $term->taxonomy),
								"title" => $term->name,
								"ID" => $id,
								"found" => $date,
							],
							$taxonomyTermId,
							$term
						);
					}

				} else if(strpos($id,"post-type/") === 0){
					$postTypeName = sanitize_text_field(str_replace("post-type/","", $id));
					$postType = get_post_type_object($postTypeName);
					if($postType instanceof \WP_Post_Type){
						return apply_filters(
							Plugin::FILTER_API_RESPONSE_GET_CONTENTS_ITEM,
							[
								"url" => get_post_type_archive_link($postTypeName),
								"post_type" => $postType->name,
								"ID" => $id,
								"found" => $date,
							],
							$postTypeName,
							$postType
						);
					}
				} else {
					if($id."" == "0"){
						return apply_filters(
							Plugin::FILTER_API_RESPONSE_GET_CONTENTS_ITEM,
							[
								"url"=>"/",
								"ID" => 0,
								"title" => _x("Home page", "get posts ajax", Plugin::DOMAIN),
								"found" => $date,
							],
							"0",
							null
						);
					} else {
						$post =  get_post($id);
						if($post instanceof \WP_Post){
							return apply_filters(
								Plugin::FILTER_API_RESPONSE_GET_CONTENTS_ITEM,
								[
									"url" => get_permalink($post->ID),
									"ID" => $post->ID,
									"title" => get_the_title($post),
									"found" => $date,
								],
								$id,
								$post
							);
						}
					}
				}

				return apply_filters(
					Plugin::FILTER_API_RESPONSE_GET_CONTENTS_ITEM,
					[
						"url" => null,
						"ID"=> $id,
						"title" => $id,
						"found" => false,
					],
					null,
					null
				);
			}, $post["content_ids"]);

			wp_send_json( $results );
			// Don't forget to stop execution afterward.
			exit;
		}
	}

	private function getParams(){
		return json_decode(file_get_contents('php://input'), true);
	}

	private function checkClient() {
		if ( $this->plugin->getClient() === null ) {
			wp_send_json_error( array( "error" => "Es konnte keine Verbindung zu Octavius aufgebaut werden." ) );
			exit;
		}
	}

	private function get_aggregation( $aggregation ) {
		switch ( $aggregation ) {
			case Arguments::AGGREGATION_MONTH:
				return Arguments::AGGREGATION_MONTH;
			default:
				return Arguments::AGGREGATION_DAY;
		}
	}

	private function get_step_size( $aggregation ) {
		switch ( $aggregation ) {
			case Arguments::AGGREGATION_MONTH:
				return " +1 month";
			default:
				return " +1 day";
		}
	}

	private function get_date_format( $aggregation ) {
		switch ( $aggregation ) {
			case Arguments::AGGREGATION_MONTH:
				return "Y-m";
			default:
				return "Y-m-d";
		}
	}
}
