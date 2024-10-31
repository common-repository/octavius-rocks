<?php


namespace OctaviusRocks\Database;


class Pageviews {

	/**
	 * @return \wpdb
	 */
	private function wpdb(){
		global $wpdb;
		return $wpdb;
	}

	/**
	 * @return string
	 */
	public function tablePostPageviews() {
		return self::wpdb()->prefix . "octavius_rocks_pageviews";
	}

	/**
	 * @param $post_id
	 * @param $hits
	 *
	 * @return false|int
	 */
	public function replacePostPageviews($post_id, $hits){
		return $this->replacePageviews("post", $post_id, $hits);
	}

	/**
	 * @param string $type
	 * @param int $content_id
	 * @param int $hits
	 *
	 * @return false|int
	 */
	private function replacePageviews( $type, $content_id, $hits ) {
	    $table = $this->tablePostPageviews();

	    $count = intval(self::wpdb()->get_var(
	        self::wpdb()->prepare(
	            "SELECT count(id) FROM $table WHERE `type` = %s AND content_id = %d",
                $type,
                $content_id
            )
        ));
	    if($count > 0){
	        return self::wpdb()->update(
	            $this->tablePostPageviews(),
                ["pageviews" => $hits],
                ["type" => $type, "content_id" => $content_id],
                ["%d"],
                ["%s", "%d"]
            );
        }

		return self::wpdb()->insert(
			$this->tablePostPageviews(),
			array(
				"type" => $type,
				"content_id" => $content_id,
				"pageviews" => $hits,
			),
			array("%s","%d","%d")
		);
	}

	/**
	 * @param int $post_id
	 *
	 * @return int
	 */
	public function getPostPageviews( $post_id ) {
		return $this->getPageviews("post", $post_id);
	}

	/**
	 * @param string $type
	 * @param int $post_id
	 *
	 * @return int
	 */
	public function getPageviews( $type, $post_id ) {
		$pageviews = self::wpdb()->get_var(
			"SELECT pageviews FROM ".$this->tablePostPageviews().
			" WHERE content_id = $post_id AND type = '$type'"
		);
		return ($pageviews)? intval($pageviews) : 0;
	}

	/**
	 * @return false|int
	 */
	public function clearPageviews(){
		return self::wpdb()->query("DELETE FROM ".$this->tablePostPageviews());
	}

	/**
	 * create the tables if not exist
	 */
	function createTables() {
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$pageviews = self::tablePostPageviews();
		\dbDelta( "CREATE TABLE IF NOT EXISTS $pageviews
		(
		 id bigint(20) unsigned auto_increment,
		 type varchar(100) NOT NULL,
		 content_id bigint(20) unsigned NOT NULL,
		 pageviews bigint(20) unsigned NOT NULL,
		 primary key (id),
		 unique key unique_content (type, content_id),
		 key (type),
		 key (content_id),
		 key (pageviews)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;" );

	}

}
