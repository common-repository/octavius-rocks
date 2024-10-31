<?php

namespace OctaviusRocks;

class PageInfo {

	/**
	 * @var \OctaviusRocks\Plugin
	 */
	var $plugin;

	/**
	 * @var string
	 */
	var $content_type = "";

	/**
	 * @var string
	 */
	var $content_id = "";

	/**
	 * @var string
	 */
	var $page_type = "";

	/**
	 * @var string
	 */
	var $viewmode = "";

	/**
	 * @var string
	 */
	var $content_url = "";

	/**
	 * @var string
	 */
	var $referer_domain = "";

	/**
	 * @var string
	 */
	var $referer_path = "";

	/**
	 * @var string
	 */
	var $screen_number = "";

	/**
	 * @var string[]
	 */
	var $tags = array();

	/**
	 * PageInfo constructor.
	 *
	 * @param $plugin
	 */
	function __construct( $plugin ) {
		$this->plugin = $plugin;

		//get page type of parent post and save it to this class
		add_action( Plugin::ACTION_PAGE_INFO, array(
			$this,
			'save_page_type',
		) );
		add_action( Plugin::ACTION_PAGE_INFO, array(
			$this,
			'save_content_type',
		) );
		add_action( Plugin::ACTION_PAGE_INFO, array(
			$this,
			'save_content_id',
		) );
		add_action( Plugin::ACTION_PAGE_INFO, array(
			$this,
			'save_tags'
		));
		add_action( Plugin::ACTION_PAGE_INFO, array(
			$this,
			'save_referer'
		));

		add_action( 'wp', array( $this, 'get_page_info' ) );
	}

	/**
	 * @return array
	 */
	function asEntity() {
		$entity = array(
			"content_type" => $this->content_type,
			"pagetype"     => $this->page_type,
			"content_id"   => $this->content_id,
		);

		if(isset($_SERVER["REQUEST_URI"])) $entity["content_url"] = strtok( $_SERVER["REQUEST_URI"], '?' );

		if(!empty($this->viewmode)) $entity["viewmode"] = $this->viewmode;

		if(!empty($this->screen_number)) $entity["screen_number"] = $this->screen_number;

		if(!empty($this->referer_domain)) $entity["referer_domain"] = $this->referer_domain;

		if(!empty($this->referer_path)) $entity["referer_path"] = $this->referer_path;

		foreach ($this->tags as $index => $tag){
			$pos = $index+1;
			if($pos < 1) continue;
			if($pos > 10) break;
			$entity["tag$pos"] = $tag;
		}

		return $entity;
	}

	/**
	 * fetch page info
	 */
	function get_page_info() {
		if ( is_admin() ) {
			return;
		}
		do_action(Plugin::ACTION_PAGE_INFO, $this);
	}

	/**
	 * lookup page type
	 */
	public function save_page_type() {

		if ( is_admin() ) {
			$this->page_type = 'admin';
		} else if ( is_home() || is_front_page() ) {
			$this->page_type = 'home';
		} else if ( is_search() ) {
			$this->page_type = "search";
		} else if ( is_author() ) {
			$this->page_type = 'author';
		} else if ( is_category() ) {
			$this->page_type = 'category';
		} else if ( is_tag() ) {
			$this->page_type = 'tag';
		} else if ( is_category() ) {
			$this->page_type = 'category';
		} else if ( is_tax() ) {
			$this->page_type = 'tax';
		} else if ( is_archive() ) {
			$this->page_type = "archive";
		} else if ( is_attachment() ) {
			$this->page_type = "attachment";
		} else if ( is_page() ) {
			$this->page_type = 'page';
		} else if ( is_single() ) {
			$this->page_type = "single";
		} else if ( is_singular() ) {
			$this->page_type = "singular";
		}

	}

	/**
	 * lookup content type
	 *
	 */
	public function save_content_type() {

		if ( "posts" == get_option( 'show_on_front' ) && ( is_front_page() || is_home() ) ) {
			$this->content_type = "posts_homepage";

			return;
		}

		if ( is_author() ) {
			$this->content_type = "author";

			return;
		}

		if ( is_category() || is_tag() || is_tax() ) {
			$this->content_type = "term";

			return;
		}

		if ( is_archive() ) {
			$this->content_type = "archive";

			return;
		}

		if ( is_search() ) {
			$this->content_type = "search";

			return;
		}

		$content_type = get_post_type();
		if ( isset( $content_type ) && $content_type != NULL ) {
			$this->content_type = $content_type;
		}
	}

	/**
	 * lookup content id
	 *
	 */
	public function save_content_id() {

		/**
		 * @var \WP_Query $wp_query
		 */
		global $wp_query;
		if ( "posts" == get_option( 'show_on_front' ) && ( is_front_page() || is_home() ) ) {
			// 0 is homepage content id if there is no page for homepage
			$this->content_id = 0;
		} else if ( is_search() ) {
			$this->content_id = "search";
		} else if ( is_author() ) {
			/**
			 * @var \WP_User $author
			 */
			$author = $wp_query->get_queried_object();
			$this->content_id = "author/" . ($author instanceof \WP_User) ? $author->ID : "";
		} else if ( is_tag() || is_tax() || is_archive() ) {

			$obj = $wp_query->get_queried_object();
			if ( $obj != NULL && $obj instanceof \WP_Term ) {
				/**
				 * @var \WP_Term $obj
				 */

				$this->content_id = "taxonomy_term/" . $obj->term_taxonomy_id;
			} else if ( $obj != NULL && $obj instanceof \WP_Post_Type ) {
				/**
				 * @var \WP_Post_Type $obj
				 */
				$this->content_id = "post-type/" . $obj->name;
			}
		} else {
			$content_id = get_the_ID();
			if ( isset( $content_id ) && $content_id != NULL && $content_id > 0 ) {
				$this->content_id = $content_id;
			}
		}
	}



	public function save_referer(){
		/**
		 * last step is referer data, so if the pixel url gets too long this gets cut out
		 */
		if ( isset( $_SERVER['HTTP_REFERER'] ) ) {
			$this->referer_domain = parse_url( $_SERVER['HTTP_REFERER'], PHP_URL_HOST );
			$this->referer_path = parse_url( $_SERVER['HTTP_REFERER'], PHP_URL_PATH );
		}
	}

	public function save_tags(){
		if(is_search()){
			/**
			 * @var \WP_Query
			 */
			global $wp_query;
			$this->tags[0] = substr(get_search_query(),0, 20);
			$this->tags[1] = $wp_query->found_posts;
			$page = get_query_var('paged');
			if(!empty($page)){
				$this->screen_number = $page;
			}
		} else if($this->plugin->page_info->page_type == "single"){
			$taxonomies = get_post_taxonomies($this->plugin->page_info->content_id);
			foreach ($taxonomies as $tax){
				if('post_format' == $tax || 'post_tag' == $tax) continue;
				$terms = get_the_terms($this->plugin->page_info->content_id, $tax);
				if(is_array($terms)){
					foreach ($terms as $term){
						/**
						 * @var \WP_Term $term
						 */
						if(count($this->tags) >= 10) break;
						$this->tags[] = $term->taxonomy."/".$term->term_id;
					}
				}
			}
			$tags = get_the_tags();
			if(is_array($tags)){
				foreach ($tags as $tag){
					if(count($this->tags) >= 10) break;
					$this->tags[] = $tag->taxonomy."/".$tag->term_id;
				}
			}
		}
	}

}
