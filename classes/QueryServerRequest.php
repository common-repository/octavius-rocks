<?php


namespace OctaviusRocks;


use OctaviusRocks\Client\Client;
use OctaviusRocks\OQL\Arguments;

class QueryServerRequest {

	/**
	 * @var false|int
	 */
	private $request_time;

	/**
	 * @var object|array|null
	 */
	private $response;

	/**
	 * @var array|Arguments
	 */
	private $args;

	/**
	 * Query constructor.
	 *
	 * @param array|Arguments $args query arguments
	 */
	function __construct( $args ) {

		$this->set_args( $args );

		$this->response     = NULL;
		$this->request_time = false;
	}

	/**
	 * set oql arguments
	 *
	 * @param array|Arguments $args
	 */
	private function set_args( $args ) {
		$this->args = $args;
	}

	/**
	 * @return array
	 * @throws OQL\InvalidQueryException
	 */
	function get_args(){
		$args = $this->args;
		if($args instanceof Arguments){
			return $args->get();
		}
		return $args;
	}

	/**
	 * check if query was executed
	 *
	 * @return bool
	 */
	function is_executed() {
		return $this->request_time !== false;
	}

	/**
	 * execute the query
	 *
	 * @throws QueryException
	 */
	public function execute() {
		if ( $this->is_executed() ) {
			throw new QueryException( "Query was already executed..." );
		}
		$this->_request();
	}

	/**
	 * Executes the array to server
	 *
	 * @return QueryServerRequest for chaining
	 * @throws QueryException
	 */
	private function _request() {

		$start = microtime( true );

		$store = ServerConfigurationStore::instance();
		$connection = ServerConfigurationStore::instance()->connect();
		if($connection == null) throw new QueryException(null,"Could not get connection.");
		$client = new Client(
			$store->get_api_key(),
			$connection
		);
		$response = $client->query($this->args);
		if($response->isError()) throw new QueryException( $response );

		$this->request_time = microtime( true ) - $start;

		$this->response = $response->getPayload();

		return $this;
	}

	/**
	 * get received data
	 *
	 * @return array
	 * @throws QueryException
	 */
	function get_data() {
		if ( ! $this->is_executed() ) {
			$this->execute();
		}

		return ( isset( $this->response["value"] ) ) ? $this->response["value"] : array();
	}

	/**
	 * @param $default_values
	 * @param $time_field_name
	 * @param $from string date
	 * @param $to string date
	 * @param $step string minute, day, hour, month
	 *
	 * @return array
	 * @throws \Exception
	 */
	function get_data_no_gaps( $from, $to, $step, $time_field_name, $default_values ) {

		$data = $this->get_data();

		switch ( $step ) {

			case "minute":
				$date_format   = "Y-m-dTH:i";
				$interval_spec = 'P1M';
				break;
			case "hour":
				$date_format   = "Y-m-dTH";
				$interval_spec = 'P1H';
				break;
			case "day":
			default:
				$date_format   = "Y-m-d";
				$interval_spec = 'P1D';
				break;

		}

		$dp = new \DatePeriod(
			new \DateTime( $from ),
			new \DateInterval( $interval_spec ),
			new \DateTime( $to )
		);

		$new_data = array();

		foreach ( $dp as $date ) {
			/**
			 * @var \DateTime $date
			 */
			$date_string = $date->format( $date_format );

			$found = array_filter( $data, function ( $item ) use ( $date_string, $time_field_name ) {
				return ( strpos( $item->{$time_field_name}, $date_string ) === 0 ) ? $item : NULL;
			} );

			if ( count( $found ) == 0 ) {
				$new_item                     = (object) $default_values;
				$new_item->{$time_field_name} = $date_string;
				$new_data[]                   = $new_item;
			} else if ( count( $found ) == 1 && $found[ array_keys( $found )[0] ] != NULL ) {
				$new_data[] = $found[ array_keys( $found )[0] ];
			} else {
				throw new \Exception( "There is more than one item for date $date_string" );
			}

		}


		return $new_data;

	}

	/**
	 * how many microseconds did the request take
	 * false is not requested yet
	 *
	 * @return int|boolean
	 */
	function get_request_time() {
		return $this->request_time;
	}

	/**
	 * get url path to octavius query endpoint
	 *
	 * @return string
	 */
	function get_query_url() {
		$store = ServerConfigurationStore::instance();
		return $store->get_server() . $store->get_server_path() . 'get/' . $store->get_api_key() . '/query';
	}
}