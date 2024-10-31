<?php


namespace OctaviusRocks\Database;

use Exception;
use OctaviusRocks\Model\QueryCacheRequest;
use OctaviusRocks\Model\QueryCacheResponse;
use WP_Error;
use wpdb;

class Queries {

	/**
	 * @var string
	 */
	private $table;

	public function __construct() {
		$this->table = $this->wpdb()->prefix . "octavius_rocks_queries";
	}

	/**
	 * @return wpdb
	 */
	private function wpdb(){
		global $wpdb;
		return $wpdb;
	}

	/**
	 * @param string $id
	 * @param QueryCacheRequest $request
	 * @param null|string $response
	 *
	 * @return bool|int|WP_Error
	 */
	public function add(string $id, QueryCacheRequest $request, $response = null){
		$count = intval(self::wpdb()->get_var(
			$this->wpdb()->prepare(
				"SELECT count(id) FROM $this->table WHERE id = %s",
				$id
			)
		));
		if($count > 0){
			return new WP_Error(500, "query with that id already exists", ["id"=>$id, "arguments" => $request->args]);
		}
		return $this->wpdb()->insert(
			$this->table,
			[
				"id" => $id,
				"arguments" => json_encode($request->args),
				"ttl" => $request->ttl,
				"update_after" => $request->updateAfter,
				"response" => is_array($response) ? json_encode($response) : $response,
				"query_touched" => date("Y-m-d H:i:s"),
				"query_fetched" => is_array($response) ? date("Y-m-d H:i:s") : null,
			]
		);
	}

	/**
	 * @param string $id
	 *
	 * @return QueryCacheResponse|null
	 */
	public function get(string $id){
		return $this->buildResponse($this->wpdb()->get_row(
			$this->wpdb()->prepare("SELECT * FROM $this->table WHERE id = %s", $id)
		));
	}

	/**
	 * @param string $relevantSince
	 *
	 * @return QueryCacheResponse[]|null[]
	 */
	public function getAll( $relevantSince = "" ) {
		if(empty($relevantSince)){
			$query = "SELECT * FROM $this->table";
		} else {
			$now = date("Y-m-d H:i:s");
			$query = $this->wpdb()->prepare(
				"SELECT * FROM $this->table WHERE query_touched > %s AND %s > DATE_ADD( query_fetched, interval update_after SECOND)",
				$relevantSince,
				$now
			);
		}
		return array_map([$this, 'buildResponse'],$this->wpdb()->get_results($query));
	}

	/**
	 * @param $row
	 *
	 * @return QueryCacheResponse|null
	 */
	private function buildResponse($row){
		if(!is_object($row) || !isset($row->id)) return null;
		try {
			return new QueryCacheResponse(
				$row->id,
				json_decode( $row->arguments, true ),
				$row->ttl,
				$row->update_after,
				null != $row->response ? json_decode( $row->response, true ) : null,
				null != $row->query_touched ? new \DateTime( $row->query_touched ) : null,
				null != $row->query_fetched ? new \DateTime( $row->query_fetched ) : null,
				null != $row->query_errored ? new \DateTime( $row->query_errored ) : null
			);
		} catch ( Exception $e ) {
			error_log("Could not build QueryCacheResponse for row id: $row->id");
			error_log($e->getMessage());
		}
		return null;
	}

	/**
	 * @param QueryCacheRequest $request
	 *
	 * @return bool|int
	 */
	public function touch( QueryCacheRequest $request ){
		return $this->wpdb()->update(
			$this->table,
			[
				"query_touched" => date("Y-m-d H:i:s"),
				"arguments" => json_encode($request->args),
				"ttl" => $request->ttl,
				"update_after" => $request->updateAfter,
			],
			[
				"id" => $request->id
			]
		);
	}

	/**
	 * @param string $id
	 * @param array $response
	 *
	 * @return bool|int
	 */
	public function updateResponse( string $id, array $response ){
		return $this->wpdb()->update(
			$this->table,
			[
				"response" => json_encode($response),
				"query_touched" => date("Y-m-d H:i:s"),
				"query_fetched" => date("Y-m-d H:i:s"),
			],
			[
				"id" => $id
			]
		);
	}

	/**
	 * @param string $id
	 * @param string $response
	 *
	 * @return bool|int
	 */
	public function updateErrorResponse(string $id, string $response){
		return $this->wpdb()->update(
			$this->table,
			[
				"response" => $response,
				"query_touched" => date("Y-m-d H:i:s"),
				"query_errored" => date("Y-m-d H:i:s"),
			],
			[
				"id" => $id
			]
		);
	}

	/**
	 * create the tables if not exist
	 */
	function createTables() {
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		\dbDelta( "CREATE TABLE IF NOT EXISTS $this->table
		(
		 id varchar(190) NOT NULL,
		 arguments text NOT NULL,
		 ttl int(10) DEFAULT 0,
		 update_after int(10) DEFAULT 0,
		 response text DEFAULT NULL,
		 response_error TEXT DEFAULT NULL,
		 query_touched DATETIME DEFAULT NULL,
		 query_fetched DATETIME DEFAULT NULL,
		 query_errored DATETIME DEFAULT NULL,
		 primary key (id),
		 key (query_touched),
		 key (query_fetched),
		 key (query_errored)		 
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;" );

	}



}
