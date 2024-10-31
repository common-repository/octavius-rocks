<?php


namespace OctaviusRocks;


use OctaviusRocks\Model\QueryCacheResponse;
use OctaviusRocks\Model\TaxonomyTermViewsQueryArgs;
use OctaviusRocks\OQL\Arguments;
use OctaviusRocks\OQL\Condition;
use OctaviusRocks\OQL\ConditionSet;
use OctaviusRocks\OQL\Field;
use PHPUnit\Exception;

/**
 * @property Plugin plugin
 */
class Repository {

	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
		$this->plugin->taxonomyTermViews->createTables();
	}

	public function getTaxonomyTermViews(TaxonomyTermViewsQueryArgs $args){
		$result = $this->plugin->taxonomyTermViews->getViews($args);
		$withTerms = array_map(function($row){
			$term = get_term_by("slug", $row->term, $row->taxonomy);
			$row->term = $term;
			return $row;
		}, $result);
		return $withTerms;
	}

	public function fetchTaxonomyTermViews(\WP_Term $term, $days = 1){
		$tagFields = [
			Field::TAG1,
			Field::TAG2,
			Field::TAG3,
			Field::TAG4,
			Field::TAG5,
			Field::TAG6,
			Field::TAG7,
			Field::TAG8,
			Field::TAG9,
			Field::TAG10,
		];
		try{
			$tax = $term->taxonomy;
			$termId = $term->term_id;
			$args = Arguments::builder();
			$args->addField(
				Field::builder(Field::HITS)
				     ->setAlias(Field::HITS)
				     ->addOperation(Field::OPERATION_SUM)
			);
			$args->addField(
				Field::builder(
					Field::TIMESTAMP
				)
			);

			$taxTermConditions = ConditionSet::builder()->useOr();
			foreach ($tagFields as $field){
				$taxTermConditions->addCondition(
					Condition::builder(
						$field,
						"$tax/$termId"
					)
				);
			}

			$conditions = ConditionSet::builder()->useAnd();
			if($days > 1){
				$days-=1;
			}
			$conditions->addCondition(
				Condition::builder(
					Field::TIMESTAMP,
					date("Y-m-d 00:00:00", strtotime("-$days days"))
				)->fieldIsGreaterThanOrEqualsValue()
			);
			$conditions->addConditionSet($taxTermConditions);

			$args->aggregation(Arguments::AGGREGATION_DAY);

			$args->setConditions($conditions);

			$query = new Query($args);
			$data = $query->get_data();
			if(!is_array($data)){
				return false;
			}
			foreach ($data as $day){
				$hits = $day[Field::HITS];
				$date = $day[Field::TIMESTAMP];
				$this->plugin->taxonomyTermViews->setDailyViews(
					$term->taxonomy,
					$term->slug,
					explode(" ", $date)[0],
					$hits
				);
			}

			return true;
		}catch (Exception $e){
			error_log($e->getMessage());
		}
		return false;
	}

	/**
	 * @param QueryCacheResponse[] $cache
	 */
	public function updateQueryCache( $cache ) {
		foreach ( $cache as $response ) {
			if ( $response instanceof QueryCacheResponse ) {
				$query = new Query( $response->arguments );
				try {
					$data = $query->get_data();
					$this->plugin->queries->updateResponse( $response->id, $data );
				} catch ( QueryException $e ) {
					error_log( "Could not update QueryCache for id: $response->id" );
					error_log( $e->getMessage() );
					$this->plugin->queries->updateErrorResponse( $response->id, $e->getMessage() );
				}
			}

		}
	}

}