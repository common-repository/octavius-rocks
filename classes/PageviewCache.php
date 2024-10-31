<?php

namespace OctaviusRocks;

use DateTime;
use DateTimeZone;
use OctaviusRocks\Database\Pageviews;
use OctaviusRocks\OQL\Arguments;
use OctaviusRocks\OQL\Condition;
use OctaviusRocks\OQL\ConditionSet;
use OctaviusRocks\OQL\Field;
use OctaviusRocks\OQL\OrderBy;

/**
 * @property Pageviews data
 */
class PageviewCache {

	const OPTION_LAST_IMPORT = "octavius_rocks_pageviews_cache_last_import";

	private $nowDateTime;

	public function __construct(Plugin $plugin) {
		$this->data = $plugin->pageviews;
	}

	/**
	 * @return string|false
	 */
	public function getLastImport(){
		return get_option(self::OPTION_LAST_IMPORT, false);
	}

	/**
	 * @param string $date
	 *
	 * @return bool
	 */
	private function setLastImport($date){
		return update_option(self::OPTION_LAST_IMPORT, $date);
	}

	/**
	 * @return bool
	 */
	public function updateLastImport(){
		try{
			return $this->setLastImport($this->now());
		} catch ( \Exception $e ) {
			error_log($e->getMessage());
		}
		return false;
	}

	/**
	 * @return bool
	 */
	public function deleteLastImport(){
		return delete_option(self::OPTION_LAST_IMPORT);
	}

	/**
	 * @param bool $overwrite
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function now($overwrite = false){
		if($this->nowDateTime == null || $overwrite){
			$nowDT  = new DateTime();
			$tz = get_option('timezone_string');
			$tz = (!empty($tz))? $tz: "UTC";
			$nowDT->setTimezone( new DateTimeZone( $tz ) );
			$this->nowDateTime = $nowDT->format( "Y-m-d H:i:s" );
		}
		return $this->nowDateTime;

	}

	/**
	 * @param int $limit
	 * @param int $page
	 *
	 * @return int|bool
	 */
	public function importPageviews($limit, $page){
		$data = $this->fetchPageviews($limit, $page, false);
		if(count($data) > 0){
			$updated = 0;
			foreach ($data as $item){
				$content_id = intval($item["content_id"]);
				if($content_id."" === $item["content_id"]){
					// seems to be a post of any type
					if($this->data->replacePostPageviews($content_id, $item["hits"])){
						$updated++;
					}
				}
			}
			return $updated;
		}
		return false;
	}

	/**
	 * @param int $limit
	 * @param int $page
	 *
	 * @return bool|int
	 */
	public function importPageviewsUpdate($limit, $page) {
		$data = $this->fetchPageviews($limit, $page, true);
		// no pageview data
		if(!is_countable($data) || count($data) <= 0) return false;

		$post_ids = array();
		foreach ($data as $item){
			$content_id = intval($item["content_id"]);
			if($content_id."" === $item["content_id"]){
				// seems to be a post of any type
				$post_ids[] = $content_id;
			}
		}

		// no int ids in pageview data
		if(count($post_ids)<= 0) return false;

		$updated = 0;
		try{
			$oql = Arguments::builder()
			                ->addField(Field::builder(Field::HITS)
			                                ->setAlias(Field::HITS)
			                                ->addOperation(Field::OPERATION_SUM)
			                )
			                ->addField(Field::builder(Field::CONTENT_ID))
			                ->setConditions(
				                ConditionSet::builder()
				                            ->addCondition(
					                            Condition::builder(Field::CONTENT_ID, $post_ids)
					                                     ->fieldIsInValues()
				                            )
				                            ->addCondition(
					                            Condition::builder(Field::TIMESTAMP, $this->now())
					                                     ->fieldIsSmallerThanOrEqualsValue()
				                            )
				                            ->addCondition(Condition::builder(Field::EVENT_TYPE, "pageview"))
			                )
			                ->groupBy(Field::CONTENT_ID);
			do_action(Plugin::ACTION_MODIFY_PAGEVIEWS_CACHE_ARGUMENTS, $oql, 2);
			$query = new Query($oql);
			$result_data = $query->get_data();
			foreach ($result_data as $hitsitem){
				if($this->data->replacePostPageviews($hitsitem["content_id"], $hitsitem["hits"])){
					$updated++;
				}
			}
		} catch ( OQL\InvalidQueryException $e ) {
			error_log($e->getMessage());
		} catch ( QueryException $e ) {
			error_log($e->getMessage());
		} catch ( \Exception $e ) {
			error_log($e->getMessage());
		}
		return $updated;
	}

	/**
	 * @param int $limit
	 * @param int $page
	 * @param bool $update
	 *
	 * @return array|bool
	 */
	public function fetchPageviews($limit, $page, $update){
		try{
			$args = Arguments::builder();
			$args->addField(Field::builder(Field::CONTENT_ID));
			$args->addField(
				Field::builder(Field::HITS)
				     ->setAlias(Field::HITS)
				     ->addOperation(Field::OPERATION_SUM)
			);

			$timeCondition = ConditionSet::builder();
			$timeCondition->addCondition(
				Condition::builder(Field::TIMESTAMP, $this->now())->fieldIsSmallerThanOrEqualsValue()
			);
			if($update && $this->getLastImport()){
				$timeCondition->addCondition(
					Condition::builder(Field::TIMESTAMP, $this->getLastImport())->fieldIsGreaterThanValue()
				)->useAnd();
			}
			$args->setConditions(
				ConditionSet::builder()->addCondition(
					Condition::builder(Field::EVENT_TYPE, "pageview")
				)->addConditionSet($timeCondition)
				 ->useAnd()
			);
			$args->orderBy(OrderBy::builder(Field::HITS)->desc());
			$args->limit($limit, $page);
			$args->groupBy(Field::CONTENT_ID);

			do_action(Plugin::ACTION_MODIFY_PAGEVIEWS_CACHE_ARGUMENTS, $args, 1);

			$query = new Query($args);
			return $query->get_data();
		} catch ( OQL\InvalidQueryException $e ) {
			error_log($e->getMessage());
		} catch ( QueryException $e ) {
			error_log($e->getMessage());
		} catch ( \Exception $e ) {
			error_log($e->getMessage());
		}
		return false;
	}

	/**
	 * clear pageviews cache
	 */
	public function clear() {
		$this->deleteLastImport();
		$this->data->clearPageviews();
	}

}
