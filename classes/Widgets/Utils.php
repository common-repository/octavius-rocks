<?php

namespace OctaviusRocks\Widgets;

use DateInterval;
use DateTime;
use DateTimeZone;
use Exception;
use OctaviusRocks\OQL\Arguments;
use OctaviusRocks\OQL\Condition;
use OctaviusRocks\OQL\ConditionSet;
use OctaviusRocks\OQL\Field;
use OctaviusRocks\OQL\InvalidQueryException;
use OctaviusRocks\OQL\OrderBy;
use OctaviusRocks\Plugin;
use OctaviusRocks\Query;
use OctaviusRocks\QueryException;
use stdClass;

/**
 * @property stdClass content
 */
class Utils {

	const ANY_POST_TYPE = "any";
	const DEFAULT_PERIOD = "7";
	const DEFAULT_COUNT = 5;
	const DEFAULT_OFFSET = 0;

	/**
	 * Utils constructor.
	 *
	 */
	public function __construct() {
		$this->content = new stdClass();
		$this->content->count       = self::DEFAULT_COUNT;
		$this->content->offset      = self::DEFAULT_OFFSET;
		$this->content->category_id = '';
		$this->content->post_type   = self::ANY_POST_TYPE;
		$this->content->relation    = 'AND';
		$this->content->period      = self::DEFAULT_PERIOD;
		$this->content->viewmode    = "";
	}

	/**
	 * @return stdClass
	 */
	public function getContent(){
		return $this->content;
	}

	/**
	 * @param stdClass $content
	 */
	public function setContent($content){
		$this->content = $content;
	}

	/**
	 *
	 * @return array
	 */
	function fetch(){
		$content = $this->content;

		$cacheId = sha1(json_encode($content));

		$arguments = new Arguments();
		$result = [];
		try {
			$arguments->addField(
				Field::builder( Field::HITS )
				     ->setAlias( "hits" )
				     ->addOperation( Field::OPERATION_SUM )
			);
			$arguments->addField( Field::builder( Field::CONTENT_ID ) );

			$typeConditions = ConditionSet::builder();

			if ( $content->relation == "AND" ) {
				$typeConditions->useAnd();
			} else {
				$typeConditions->useOr();
			}

			if ( isset( $content->category_id ) && ! empty( $content->category_id ) ) {
				$category_id = $content->category_id;
				$set         = ConditionSet::builder();
				$set->useOr();
				for ( $i = 1; $i <= 10; $i ++ ) {
					$set->addCondition(
						Condition::builder( "tag$i", "category/$category_id" )
					);
				}
				$typeConditions->addConditionSet( $set );
			}

			$taxonomies = $this->getTaxonomies();
			foreach ($taxonomies as $pair){
				$set      = ConditionSet::builder();
				$set->useOr();
				$taxonomy = $pair["taxonomy"];
				$term_id = $pair["term_id"];
				for ( $i = 1; $i <= 10; $i ++ ) {
					$set->addCondition(
						Condition::builder( "tag$i", "$taxonomy/$term_id" )
					);
				}
				$typeConditions->addConditionSet( $set );
			}

			// check if post type is set
			$post_types = ( isset( $content->post_type ) && ! empty( $content->post_type ) )? $content->post_type: self::ANY_POST_TYPE;

			// if is old version of string param convert it to array
			if( is_string($post_types) ){
				if( $post_types === self::ANY_POST_TYPE ){
					$post_types = $this->getPostTypeSlugs();
				} else {
					$post_types = array($post_types);
				}
			}

			if(!is_array($post_types)) $post_types = array($post_types);

			// if the any post type is exists overwrite with all post types
			if(in_array(self::ANY_POST_TYPE, $post_types)){
				$post_types = $this->getPostTypeSlugs();
			}

			$postTypeConditions = ConditionSet::builder();
			$postTypeConditions->useOr();
			foreach ($post_types as $post_type){
				$postTypeConditions->addCondition(
					Condition::builder( Field::CONTENT_TYPE, $post_type )
				);
			}
			$typeConditions->addConditionSet($postTypeConditions);


			$period = $content->period ?: self::DEFAULT_PERIOD;
			$nowDT  = new DateTime();
			$nowDT->setTimezone( new DateTimeZone( wp_timezone_string()) );
			$now = $nowDT->format( "Y-m-d H:i:00" );
			$nowDT->sub( new DateInterval( "P" . $period . "D" ) );
			$periodAgo        = $nowDT->format( "Y-m-d H:i:00" );
			$periodConditions = ConditionSet::builder()
			                                ->useAnd()
			                                ->addCondition(
				                                Condition::builder( Field::TIMESTAMP, $now )
				                                         ->fieldIsSmallerThanOrEqualsValue()
			                                )
			                                ->addCondition(
				                                Condition::builder( Field::TIMESTAMP, $periodAgo )
				                                         ->fieldIsGreaterThanOrEqualsValue()
			                                );

			$arguments->setConditions(
				ConditionSet::builder()
				            ->useAnd()
				            ->addConditionSet( $typeConditions )
				            ->addConditionSet( $periodConditions )
				            ->addCondition(
					            Condition::builder( Field::CONTENT_ID, "" )
					                     ->notEqual()
				            )
			);
			$arguments->groupBy( Field::CONTENT_ID );
			$arguments->orderBy(
				OrderBy::builder( Field::HITS )->desc()
			);

			$count  = isset( $content->count ) ? intval( $content->count ) : self::DEFAULT_COUNT;
			$offset = isset( $content->offset ) ? intval( $content->offset ) : self::DEFAULT_OFFSET;

			$arguments->limit( $count + $offset, 0 );

			$query = new Query(
				$arguments,
				Plugin::instance()->getDefaultCacheConfig($cacheId)
			);
			$result = $query->get_data();

		} catch ( InvalidQueryException $e ) {
			error_log( $e->getMessage() );
		} catch ( QueryException $e ) {
			error_log( $e->getMessage() );
		} catch ( Exception $e ) {
			error_log( $e->getMessage() );
		}

		return $result;
	}

	/**
	 * @return array of items with shape  array( "key" => "viewmode", "text" =>
	 *     "My viewmode" )
	 */
	function getViewmodes() {
		return apply_filters( Plugin::FILTER_GRID_VIEWMODES, array() );
	}

	function showTaxonomy($taxonomy){
		return apply_filters( Plugin::FILTER_GRID_BOX_SHOW_TAXONOMY, true, $taxonomy);
	}

	public function getTaxonomiesForQueryArgs(){
		$taxonomies = get_taxonomies( array(
			'public' => true,
		), 'object' );
		$result = [];
		foreach ( $taxonomies as $tax ) {
			if(!$this->showTaxonomy($tax->name)) continue;
			$result[] = $tax->name;
		}
		return $result;
	}


	public function getPostTypes(){
		$input      = get_post_types( array(
			'public' => true,
		), 'objects' );
		$allowed_post_types = array();
		foreach ( $input as $post_type => $info ) {
			if( apply_filters( Plugin::FILTER_GRID_BOX_SHOW_POST_TYPE, true, $post_type) ) {
				$allowed_post_types[$post_type] = $info;
			};
		}
		return $allowed_post_types;
	}

	public function getPostTypeSlugs(){
		$slugs = array();
		foreach ($this->getPostTypes() as $slug => $info){
			$slugs[] = $slug;
		}
		return $slugs;
	}

	public function getTaxonomies($withEmpty = false){
		$taxonomies = array();
		foreach ( $this->content as $key => $value ) {
			// if not tax field or has no value
			$taxonomy = $this->getTaxonomy($key);
			if ( null == $taxonomy) {
				continue;
			}
			if(empty($value) && !$withEmpty){
				continue;
			}
			$taxonomies[] = array(
				"taxonomy" => $taxonomy,
				"term_id" => $value,
			);
		}
		return $taxonomies;
	}

	public function getTaxonomy($key){
		if ( strpos( $key, "tax_" ) !== 0 ) {
			return null;
		}
		return str_replace( "tax_", "", $key );
	}

	public function getTaxonomyKey($taxonomy){
		return 'tax_'.$taxonomy;
	}


	public function contentStructure() {
		$cs = [];

		$cs[] = array(
			"key"   => "count",
			"label" => __( 'Count', Plugin::DOMAIN ),
			"type"  => "number",
		);

		$cs[] = array(
			"key"   => "offset",
			"label" => __( 'Offset', Plugin::DOMAIN ),
			"type"  => "number",
		);

		/**
		 * category select
		 */
		if($this->showTaxonomy('category')){
			$terms = get_categories( array(
				'hide_empty' => false,
			) );

			$cat = get_taxonomy("category");

			$selections = array(
				array(
					'key'  => '',
					'text' => __( '-- All --', Plugin::DOMAIN ),
				),
			);
			foreach ( $terms as $term ) {
				/**
				 * @var \WP_Term $term
				 */
				$selections[] = array(
					'key'  => $term->term_id,
					'text' => $term->name,
				);
			}
			$cs[] = array(
				'key'        => 'category_id',
				'label'      => __( $cat->label, Plugin::DOMAIN ),
				'type'       => 'select',
				'selections' => $selections,
			);
		}


		/**
		 * taxonomies
		 */
		$taxonomies = get_taxonomies( array(
			'public' => true,
		), 'object' );
		foreach ( $taxonomies as $tax ) {
			/**
			 * post format is a special case so ignore
			 */

			if ( 'category' == $tax->name || !$this->showTaxonomy($tax->name) ) {
				continue;
			}

			/**
			 * add taxonomy to content structure
			 */
			$selections = array();
			$selections[] = array(
				'key'  => '',
				'text' => __( '-- All --', Plugin::DOMAIN ),
			);
			$terms = get_terms( array("taxonomy" => $tax->name), array('hide_empty' => false));
			foreach ($terms as $term){
				/**
				 * @var \WP_Term $term
				 */
				$selections[] = array( 'key' => $term->term_id, 'text' => $term->name );
			}
			$cs[] = array(
				'key'   => 'tax_'.$tax->name,
				'label' => $tax->label,
				'type'  => 'select',
				'selections'=> $selections,
			);
		}

		/**
		 * relation type
		 */
		$cs[] = array(
			'key'        => 'relation',
			'label'      => __( 'Relation type', Plugin::DOMAIN ),
			'type'       => 'select',
			'selections' => array(
				array( 'key' => 'OR', 'text' => 'OR' ),
				array( 'key' => 'AND', 'text' => 'AND' ),
			),
		);

		/**
		 * post type select
		 */
		$post_types = array();
		$post_types[] = array(
			'key'  => self::ANY_POST_TYPE,
			'text' => __( 'Any post type', Plugin::DOMAIN ),
		);
		$input = $this->getPostTypes();
		foreach ( $input as $post_type => $info ) {
			$post_types[] = array(
				'key'  => $post_type,
				'text' => $info->labels->name,
			);
		}
		$cs[]         = array(
			'key'        => 'post_type',
			'label'      => __( 'Post type', Plugin::DOMAIN ),
			'type'       => 'select',
			'selections' => $post_types,
			'multiple'   => true,
		);

		/**
		 * period
		 */
		$cs[] = array(
			'key'        => 'period',
			'label'      => __( 'Period', Plugin::DOMAIN ),
			'type'       => 'select',
			'selections' => array(
				array( 'key' => '1', 'text' => '24 hours' ),
				array( 'key' => '3', 'text' => '96 hours' ),
				array( 'key' => '5', 'text' => '5 days' ),
				array( 'key' => '7', 'text' => '7 days' ),
				array( 'key' => '10', 'text' => '10 days' ),
				array( 'key' => '14', 'text' => '14 days' ),
				array( 'key' => '30', 'text' => '30 days' ),
			),
		);

		/**
		 * viewmodes
		 */
		$viewmodes = $this->getViewmodes();
		if ( count( $viewmodes ) > 0 ) {
			$cs[] = array(
				'key'        => 'viewmode',
				'label'      => __( 'Viewmode', Plugin::DOMAIN ),
				'type'       => 'select',
				'selections' => $viewmodes,
			);
		}

		return $cs;
	}



}
