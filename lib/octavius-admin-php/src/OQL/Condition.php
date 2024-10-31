<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 13.11.18
 * Time: 18:16
 */

namespace OctaviusRocks\OQL;


class Condition {

	/**
	 * @var string
	 */
	private $field;

	/**
	 * @var int|string|string[]|int[]|null
	 */
	private $value;

	/**
	 * @var string
	 */
	private $compare;

	/**
	 * @param string $field
	 * @param string|string[]|int|int[]|null $value
	 *
	 * @return Condition
	 * @throws InvalidQueryException
	 */
	public static function builder( $field, $value ) {
		return new Condition( $field, $value );
	}

	/**
	 * GroupBy constructor.
	 *
	 * @param string $field
	 * @param string|string[]|int|int[]|null $value
	 *
	 * @throws InvalidQueryException
	 */
	public function __construct( $field, $value ) {
		$this->field = $field;
		if( $value === null){
			$this->value = null;
			$this->fieldIsNotNull();
		} else if ( $this->isValidValue( $value ) ) {
			$this->value = $value;
			$this->equals();
		} else if ( is_array( $value ) ) {
			$this->value = array();
			foreach ( $value as $v ) {
				if ( ! $this->isValidValue( $v ) ) {
					throw new InvalidQueryException( "Invalid value for condition." );
				}
				$this->value[] = $v;
			}
			$this->fieldIsInValues();
		}
	}

	/**
	 * @param $value
	 *
	 * @return bool
	 */
	private function isValidValue( $value ) {
		return is_string( $value ) || is_numeric( $value ) || $value === null;
	}

	/**
	 * @return bool
	 */
	private function isSingleValue() {
		return !is_array($this->value) && $this->isValidValue( $this->value );
	}

	/**
	 * @return $this
	 * @throws InvalidQueryException
	 */
	public function equals() {
		if ( ! $this->isSingleValue() ) {
			throw new InvalidQueryException( "You can only use IN or NOT IN with array values" );
		}
		$this->compare = "=";

		return $this;
	}

	/**
	 * @return $this
	 * @throws InvalidQueryException
	 */
	public function notEqual(){
		if ( ! $this->isSingleValue() ) {
			throw new InvalidQueryException( "You can only use IN or NOT IN with array values" );
		}
		$this->compare = "!=";

		return $this;
	}

	/**
	 * @return $this
	 * @throws InvalidQueryException
	 */
	public function fieldIsGreaterThanValue() {
		if ( ! $this->isSingleValue() ) {
			throw new InvalidQueryException( "You can only use IN or NOT IN with array values" );
		}
		$this->compare = ">";

		return $this;
	}

	/**
	 * @return $this
	 * @throws InvalidQueryException
	 */
	public function fieldIsGreaterThanOrEqualsValue() {
		if ( ! $this->isSingleValue() ) {
			throw new InvalidQueryException( "You can only use IN or NOT IN with array values" );
		}
		$this->compare = ">=";

		return $this;
	}

	/**
	 * @return $this
	 * @throws InvalidQueryException
	 */
	public function fieldIsSmallerThanValue() {
		if ( ! $this->isSingleValue() ) {
			throw new InvalidQueryException( "You can only use IN or NOT IN with array values" );
		}
		$this->compare = "<";

		return $this;
	}

	/**
	 * @return $this
	 * @throws InvalidQueryException
	 */
	public function fieldIsSmallerThanOrEqualsValue() {
		if ( ! $this->isSingleValue() ) {
			throw new InvalidQueryException( "You can only use IN or NOT IN with array values" );
		}
		$this->compare = "<=";

		return $this;
	}

	/**
	 * @return $this
	 * @throws InvalidQueryException
	 */
	public function fieldIsInValues() {
		if ( $this->isSingleValue() ) {
			throw new InvalidQueryException( "You can not use IN or NOT IN with not array single values." );
		}
		$this->compare = "IN";

		return $this;
	}

	/**
	 * @return $this
	 * @throws InvalidQueryException
	 */
	public function fieldIsNotInValues() {
		if ( $this->isSingleValue() ) {
			throw new InvalidQueryException( "You can not use IN or NOT IN with not array single values." );
		}
		$this->compare = "NOT IN";

		return $this;
	}

	public function fieldIsNull(){
		$this->compare = "IS";
		$this->value = null;
		return $this;
	}

	public function fieldIsNotNull(){
		$this->compare = "IS NOT";
		$this->value = null;
		return $this;
	}

	/**
	 * @return array
	 */
	public function get() {
		return array(
			"field"   => $this->field,
			"value"   => $this->value,
			"compare" => $this->compare,
		);
	}

	/**
	 * @return false|string
	 */
	public function __toString() {
		return json_encode( $this->get() );
	}
}