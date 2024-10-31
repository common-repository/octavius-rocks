<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 14.11.18
 * Time: 17:51
 */

namespace OctaviusRocks\OQL;

class ConditionSet {

	/**
	 * @var array
	 */
	private $items = array();
	private $relation = "AND";

	/**
	 * @return ConditionSet
	 */
	public static function builder(){
		return new ConditionSet();
	}

	/**
	 * @param ConditionSet $condition_set
	 *
	 * @return $this
	 */
	public function addConditionSet(ConditionSet $condition_set){
		$this->items[] = $condition_set;
		return $this;
	}

	/**
	 * @param Condition $condition
	 *
	 * @return $this
	 */
	public function addCondition(Condition $condition){
		$this->items[] = $condition;
		return $this;
	}

	/**
	 * switch to AND connection for this set of conditions
	 * @return $this
	 */
	public function useAnd(){
		$this->relation = "AND";
		return $this;
	}

	/**
	 * switch to OR connection for this set of conditions
	 * @return $this
	 */
	public function useOr(){
		$this->relation = "OR";
		return $this;
	}

	/**
	 * @return false|string
	 */
	public function __toString() {
		return json_encode(array(
			"relation" => $this->relation,
			"conditions" => $this->items
		));
	}

	/**
	 * @return array|null
	 */
	public function get(){
		if(empty($this->items)){
			return null;
		}
		$conditions = array(
			"entries" => array(),
			"relation" => $this->relation,
		);
		foreach ($this->items as $condition){
			/**
			 * @var ConditionSet|Condition
			 */
			$cond = $condition->get();
			if($cond == null) continue;
			$conditions["entries"][] = $cond;
		}
		return $conditions;
	}
}