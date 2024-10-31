<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 13.11.18
 * Time: 18:16
 */

namespace OctaviusRocks\OQL;


class OrderBy {

	/**
	 * @var string
	 */
	private $field;
	/**
	 * @var string
	 */
	private $direction;

	/**
	 * @param $field
	 *
	 * @return OrderBy
	 */
	public static function builder($field){
		return new OrderBy($field);
	}

	/**
	 * GroupBy constructor.
	 *
	 * @param string $field
	 */
	public function __construct( $field) {
		$this->field = $field;
		$this->asc();
	}

	/**
	 * sets order to ascending
	 */
	public function asc(){
		$this->direction = "ASC";
		return $this;
	}

	/**
	 * sets order to descending
	 */
	public function desc(){
		$this->direction = "DESC";
		return $this;
	}

	/**
	 * @return array
	 */
	public function get(){
		return array(
			"field"=>$this->field,
			"direction" => $this->direction
		);
	}

	/**
	 * @return false|string
	 */
	public function __toString() {
		return json_encode($this->get());
	}
}