<?php


namespace OctaviusRocks;


/**
 * @property Plugin plugin
 */
class PageviewsWPQuery {

	const VAR_PAGEVIEWS = "pageviews";

	/**
	 * PageviewsWPQuery constructor.
	 *
	 * @param Plugin $plugin
	 */
	public function __construct(Plugin $plugin) {
		$this->plugin = $plugin;
		add_filter( 'posts_join', array( $this, 'posts_join' ), 10, 2 );
		add_filter( 'posts_orderby', array($this, 'order_by'), 10, 2 );
	}

	/**
	 * @param \WP_Query $wp_query
	 * @return boolean
	 */
	public function isPageviewsNeeded($wp_query){

		// if in orderby
		if( !empty($wp_query->get('orderby')) ){
			$orderby = $wp_query->get("orderby");
			if(is_array($orderby)){
				if(in_array(self::VAR_PAGEVIEWS, array_keys($orderby))) return true;
			} else {
				$parts =  explode(" ",$wp_query->get("orderby"));
				if(in_array(self::VAR_PAGEVIEWS, $parts)) return true;
			}
		}

		return false;
	}

	/**
	 * JOIN statement
	 *
	 * @param  string $join The JOIN clause of the query.
	 *
	 * @param \WP_Query $wp_query
	 *
	 * @return string $join
	 */
	function posts_join( $join, $wp_query ) {
		if($this->isPageviewsNeeded($wp_query)){
			global $wpdb;
			$table = $this->plugin->pageviews->tablePostPageviews();
			$join .= "LEFT JOIN $table ON ({$wpdb->posts}.ID = $table.content_id AND $table.type = 'post')";
		}

		return $join;
	}

	/**
	 * @param string $orderby
	 * @param \WP_Query $wp_query
	 *
	 * @return string
	 */
	public function order_by($orderby, $wp_query){

		if(!$this->isPageviewsNeeded($wp_query)) return $orderby;

		$direction = $wp_query->get("order", "desc");
		$comma   = ( $orderby ) ? "," : "";
		return $this->plugin->pageviews->tablePostPageviews() . ".pageviews " . $direction . $comma . $orderby;
	}
}