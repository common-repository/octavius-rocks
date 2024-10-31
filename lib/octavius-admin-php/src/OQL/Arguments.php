<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 13.11.18
 * Time: 17:46
 */

namespace OctaviusRocks\OQL;



class Arguments {

	const AGGREGATION_MINUTES_5 = 2;
	const AGGREGATION_HOUR      = 3;
	const AGGREGATION_DAY       = 4;
	const AGGREGATION_MONTH     = 5;

	/**
	 * @var array
	 */
	private $args = array(
		"fields" => array(),
	);

	/**
	 * Arguments constructor.
	 */
	public function __construct() {
		Field::ensureConstants();
	}

	/**
	 * @return Arguments
	 */
	public static function builder(){
		return new Arguments();
	}

	/**
	 * @param string $name
	 *
	 * @return Field
	 * @throws InvalidQueryException
	 */
	public function buildField( $name ){
		return new Field($name);
	}

	/**
	 * @param Field $field
	 *
	 * @return Arguments
	 */
	public function addField(Field $field) {
		$this->args["fields"][] = $field;
		return $this;
	}

	/**
	 * @return ConditionSet
	 */
	public function buildConditionSet(){
		return new ConditionSet();
	}

	/**
	 * @param ConditionSet $conditions
	 *
	 * @return Arguments
	 */
	public function setConditions( ConditionSet $conditions ) {
		$this->args["conditions"] = $conditions;
		return $this;
	}

	/**
	 * @param string $field
	 *
	 * @return OrderBy
	 */
	public function buildOrderBy( $field ){
		return new OrderBy($field);
	}

	/**
	 * @param OrderBy|OrderBy[] $fieldOrFields
	 *
	 * @return Arguments
	 * @throws InvalidQueryException
	 */
	public function orderBy( $fieldOrFields ) {
		if ( $fieldOrFields instanceof OrderBy ) {
			$this->args["order_by"] = array( $fieldOrFields );
		} else {
			$this->args["order_by"] = array();
			foreach ( $fieldOrFields as $field ) {
				if ( ! ( $field instanceof OrderBy ) ) {
					throw new InvalidQueryException( "Field is not valid {$field}" );
				}
				$this->args["order_by"][] = $field;
			}
		}
		return $this;
	}

	/**
	 * @param string|string[] $fieldOrFields
	 *
	 * @return Arguments
	 * @throws InvalidQueryException
	 */
	public function groupBy( $fieldOrFields ) {
		if ( is_string( $fieldOrFields ) ) {
			$this->args["group_by"] = array( $fieldOrFields );
		} else {
			$this->args["group_by"] = array();
			foreach ( $fieldOrFields as $field ) {
				if ( ! is_string( $field ) ) {
					throw new InvalidQueryException( "Field is not valid {$field}" );
				}
				$this->args["group_by"][] = $field;
			}
		}

		return $this;
	}

	/**
	 * @param int $number_of_rows
	 * @param int $page
	 *
	 * @return Arguments
	 */
	public function limit( $number_of_rows, $page ) {
		$this->args["limit"] = $number_of_rows;
		$this->args["page"]  = $page;

		return $this;
	}

	/**
	 * Use aggregation constants
	 *
	 * @param $aggregation
	 *
	 * @return Arguments
	 * @throws InvalidQueryException
	 */
	public function aggregation( $aggregation ) {
		switch ( $aggregation ) {
			case self::AGGREGATION_MINUTES_5:
				$this->args["aggregation"] = "minutes_5";
				break;
			case self::AGGREGATION_HOUR:
				$this->args["aggregation"] = "hour";
				break;
			case self::AGGREGATION_DAY:
				$this->args["aggregation"] = "day";
				break;
			case self::AGGREGATION_MONTH:
				$this->args["aggregation"] = "month";
				break;
			default:
				throw new InvalidQueryException( "Unsupported aggregation code {$aggregation}" );
		}

		return $this;
	}

	/**
	 * @return array
	 * @throws InvalidQueryException
	 */
	public function get() {

		if(empty($this->args["fields"])){
			throw new InvalidQueryException("You need to specify at least one field.");
		}

		$args = array();

		foreach ( $this->args as $key => $arg ) {
			switch ( $key ) {
				case "fields":
					/**
					 * @var Field[] $arg
					 */
					foreach ($arg as $field){
						$args[$key][] = $field->get();
					}
					break;
				case "conditions":
					/**
					 * @var ConditionSet $arg
					 */
					$conditions = $arg->get();
					if($conditions != null){
						$args[$key] = $conditions;
					}
					break;
				case "order_by":
					$args[$key] = array();
					foreach ($arg as $orderBy){
						/**
						 * @var OrderBy $orderBy
						 */
						$args[$key][] = $orderBy->get();
					}

					break;
				default:
					$args[$key] = $arg;
					break;
			}
		}

		return $args;
	}
}