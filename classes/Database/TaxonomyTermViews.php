<?php


namespace OctaviusRocks\Database;


use OctaviusRocks\Components\Database;
use OctaviusRocks\Model\TaxonomyTermViewsQueryArgs;

/**
 * @property string $table
 */
class TaxonomyTermViews extends Database {

	function init() {
		$this->table = $this->wpdb->prefix . "octavius_rocks_taxonomy_term_views";
	}

	function getFirstPeriod(){
		return $this->wpdb->get_var(
			"SELECT views_period FROM $this->table ORDER BY views_period ASC LIMIT 1"
		);
	}

	function getTaxonomies(){
		return $this->wpdb->get_col(
			"SELECT DISTINCT taxonomy FROM $this->table"
		);
	}

	function getViews( TaxonomyTermViewsQueryArgs $args ) {

		$where   = [];
		$where[] = $this->wpdb->prepare( "views_period >= %s", $args->from );
		$where[] = $this->wpdb->prepare( "views_period <= %s", $args->until );

		if($args->taxonomy){
			$where[] = $this->wpdb->prepare("taxonomy = %s", $args->taxonomy);
		}

		$where = ( count( $where ) > 0 ) ? "WHERE " . join( " AND ", $where ) : "";

		$limit = $args->numberOfElements;
		$offset = ($args->page-1) * $limit;

		$result = $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT term, taxonomy, sum(views) as views FROM $this->table $where GROUP BY term, taxonomy ORDER BY views DESC LIMIT $offset, $limit",
			)
		);
		return $result;
	}

	function setDailyViews( $taxonomy, $term, $period, $views ) {
		$this->wpdb->replace(
			$this->table,
			[
				"taxonomy"      => $taxonomy,
				"term"          => $term,
				"views_period" => $period,
				"views"         => $views,
			]
		);
	}


	/**
	 * create the tables if not exist
	 */
	function createTables() {
		parent::createTables();
		\dbDelta( "CREATE TABLE IF NOT EXISTS $this->table
		(
		 id bigint(20) unsigned auto_increment,
		 taxonomy varchar (32) NOT NULL,
    	 term varchar (190) NOT NULL,
		 views_period varchar(30) NOT NULL,
		 views bigint(20) unsigned NOT NULL,
		 primary key (id),
		 unique key unique_views (taxonomy, term, views_period),
		 key (taxonomy),
    	 key(term),
	     key (views_period),
    	 key (views)
    	 
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;" );

	}
}
