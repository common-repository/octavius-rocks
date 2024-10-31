<?php

use OctaviusRocks\Model\CacheConfig;
use OctaviusRocks\OQL\Arguments;
use OctaviusRocks\Plugin;
use OctaviusRocks\Query;
use OctaviusRocks\QueryException;

/**
 * @return Plugin
 */
function octavius_rocks_get_plugin() {
	return Plugin::instance();
}

/**
 * request octavius data
 *
 * @param array|Arguments $args
 * @param string|null $cacheId
 * @param int $ttl
 * @param int|null $updateAfter needs to be less than ttl to work properly
 *
 * @return Query
 */
function octavius_rocks_query( $args, $cacheId = null, $ttl = 0, $updateAfter = null ) {
	$cacheConfig = !empty($cacheId) ? CacheConfig::build($cacheId)->setTTL($ttl) : null;
	if($cacheConfig instanceof CacheConfig && null != $updateAfter){
		$cacheConfig->setUpdateAfter($updateAfter);
	}
	return new Query(
		$args,
		$cacheConfig
	);
}

/**
 *
 * @return Query|null
 */
function octavius_rocks_get_query() {
	return Query::get_instance();
}

/**
 * get top list of minutes in past
 *
 * @param int $minutes in the past
 * @param int $limit entries
 * @param int|null $ttl
 * @param int|null $updateAfter
 *
 * @return array
 * @throws QueryException
 */
function octavius_rocks_query_top_contents( $minutes = 20160, $limit = 15, $ttl = null, $updateAfter = null ) {
	$query = octavius_rocks_query(
		array(
			"fields"     => array(
				array(
					"field"      => "hits",
					"operations" => array( "sum" ),
					"as"         => "hits",
				),
				"content_id",
			),
			"conditions" => array(
				"entries"  => array(
					array(
						"field"   => "timestamp",
						"value"   => date( "Y-m-d H:i:s", current_time( 'timestamp' ) - 60 * $minutes ),
						"compare" => ">=",
					),
					array(
						"field" => "content_id",
						"value" => "",
						"compare" => "!="
					),
					array(
						"field" => "content_type",
						"value" => "post",
						"compare" => "=",
					),
				),
				"relation" => "AND",
			),
			"page"       => 1,
			"limit"      => $limit,
			"group_by"   => array( "content_id" ),
			"order_by"   => array(
				array(
					"field" => "hits",
					"direction" => "DESC",
				),
			),
		),
		"_top_contents_{$minutes}_{$limit}",
		null != $ttl && intval($ttl) >= 0 ? $ttl : 0,
		$updateAfter
	);
	return $query->get_data();
}

/**
 * get hits of post grouped by event_type
 *
 * @param $post_id
 * @param int|null $ttl
 * @param int|null $updateAfter
 *
 * @return array
 * @throws QueryException
 */
function octavius_rocks_query_for_post( $post_id, $ttl = null, $updateAfter = null ) {
	$query = octavius_rocks_query(
		array(
			"fields"     => array(
				array(
					"field"      => "hits",
					"operations" => array( "sum" ),
					"as"         => "hits",
				),
				"event_type",
			),
			"conditions" => array(
				"entries"  => array(
					array(
						"field"   => "content_id",
						"value"   => $post_id,
						"compare" => "=",
					),
				),
				"relation" => "AND",
			),
			"group_by"   => array( "event_type" ),
		),
		"_query_for_post_{$post_id}",
		null != $ttl && intval($ttl) >= 0 ? $ttl : 0,
		$updateAfter
	);
	return $query->get_data();
}

/**
 * builds data attributes string
 * @param $args
 *
 * @return string
 */
function octavius_rocks_get_attributes($args){
	return octavius_rocks_get_plugin()->tracker->get_octavius_attributes_string( $args );
}

/**
 *  get html attributes string
 *
 * @deprecated replace with octavius_rocks_get_attributes_string
 */
function octavius_client_data_builder( $args ) {
	return octavius_rocks_get_plugin()->tracker->get_octavius_attributes_string( $args );
}
