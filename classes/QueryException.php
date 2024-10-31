<?php

namespace OctaviusRocks;

use OctaviusRocks\Server\Response;

class QueryException extends \Exception {

	public $response = "";

	/**
	 * QueryException constructor.
	 *
	 * @param \OctaviusRocks\Server\Response|null $response
	 * @param string $message
	 * @param int $code
	 * @param \Throwable|NULL $previous
	 */
	public function __construct( $response, $message = "Octavius Query Exception", $code = 0, \Throwable $previous = NULL ) {

		$additional = "";
		if($response instanceof Response){
			$add = $response->getPayload("additional");
			$additional = (is_array($add)) ? implode(",", $add): "";
		}

		parent::__construct(
			$message." -> ".$additional,
			$code,
			$previous
		);
		$this->response = $response;
	}
}
