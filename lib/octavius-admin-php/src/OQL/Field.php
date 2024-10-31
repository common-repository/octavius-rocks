<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 13.11.18
 * Time: 18:16
 */

namespace OctaviusRocks\OQL;

class Field {

	/**
	 * valid field names
	 */
	const CONTENT_ID = "content_id";
	const CONTENT_URL = "content_url";
	const PARENT_ID = "parent_id";
	const PARENT_URL = "parent_url";
	const CONTENT_TYPE = "content_type";
	const BREAKPOINT = "breakpoint";
	const REGION = "region";
	const PAGETYPE = "pagetype";
	const VIEWMODE = "viewmode";
	const VARIANT = "variant";
	const SCREEN_NUMBER = "screen_number";
	const REFERER_DOMAIN = "referer_domain";
	const REFERER_PATH = "referer_path";
	const HITS = "hits";
	const TIMESTAMP = "timestamp";
	const EVENT_TYPE = "event_type";
	const TAG1 = "tag1";
	const TAG2 = "tag2";
	const TAG3 = "tag3";
	const TAG4 = "tag4";
	const TAG5 = "tag5";
	const TAG6 = "tag6";
	const TAG7 = "tag7";
	const TAG8 = "tag8";
	const TAG9 = "tag9";
	const TAG10 = "tag10";

	/**
	 * valid operations
	 */
	const OPERATION_AVG             = "avg";
	const OPERATION_SUM             = "sum";
	const OPERATION_MIN             = "min";
	const OPERATION_MAX             = "max";
	const OPERATION_GRAIN_MINUTES_5 = "grain_minutes_5";
	const OPERATION_GRAIN_HOUR      = "grain_hour";
	const OPERATION_GRAIN_DAY       = "grain_day";
	const OPERATION_GRAIN_MONTH     = "grain_month";


	const VALID_OPERATIONS = array(
		Field::OPERATION_AVG,
		Field::OPERATION_SUM,
		Field::OPERATION_MIN,
		Field::OPERATION_MAX,
		Field::OPERATION_GRAIN_MINUTES_5,
		Field::OPERATION_GRAIN_HOUR,
		Field::OPERATION_GRAIN_DAY,
		Field::OPERATION_GRAIN_MONTH,
	);

	const VALID_FIELDS = array(
		Field::CONTENT_ID,
		Field::CONTENT_URL,
		Field::PARENT_ID,
		Field::PARENT_URL,
		Field::CONTENT_TYPE,
		Field::BREAKPOINT,
		Field::REGION,
		Field::PAGETYPE,
		Field::VIEWMODE,
		Field::VARIANT,
		Field::SCREEN_NUMBER,
		Field::REFERER_DOMAIN,
		Field::REFERER_PATH,
		Field::HITS,
		Field::TIMESTAMP,
		Field::EVENT_TYPE,
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
	);

	/**
	 * @var string
	 */
	private $name;
	/**
	 * @var null|string
	 */
	private $alias = NULL;
	/**
	 * @var string[]
	 */
	private $operations = array();

	/**
	 * @param $field_name
	 *
	 * @return Field
	 * @throws InvalidQueryException
	 */
	public static function builder($field_name){
		return new Field($field_name);
	}

	/**
	 * GroupBy constructor.
	 *
	 * @param string $field_name
	 *
	 * @throws InvalidQueryException
	 */
	public function __construct( $field_name ) {
		if ( ! in_array( $field_name, static::VALID_FIELDS ) ) {
			throw new InvalidQueryException( "Unknown field: $field_name" );
		}
		$this->name = $field_name;
	}

	/**
	 * @param string $name
	 *
	 * @return Field
	 */
	public function setAlias( $name ) {
		$this->alias = $name;

		return $this;
	}

	/**
	 * @param string $operation
	 *
	 * @return $this
	 * @throws InvalidQueryException
	 */
	public function addOperation( $operation ) {
		if ( ! in_array( $operation, static::VALID_OPERATIONS ) ) {
			throw new InvalidQueryException( "Unknown operation on field: $operation" );
		}
		$this->operations[] = $operation;

		return $this;
	}

	/**
	 * @return array
	 * @throws InvalidQueryException
	 */
	public function get() {
		$args          = array();
		$args["field"] = $this->name;
		if ( ! empty( $this->operations ) ) {
			$args["operations"] = $this->operations;
			if ( $this->alias == NULL ) {
				throw new InvalidQueryException( "You need to specify an alias for the field if you use operations" );
			}
		}
		if ( ! empty( $this->alias ) ) {
			$args["as"] = $this->alias;
		}

		return $args;
	}

	static function ensureConstants(){

	}

	/**
	 * @return false|string
	 * @throws InvalidQueryException
	 */
	public function __toString() {
		return json_encode( $this->get() );
	}
}