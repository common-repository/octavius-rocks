<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 14.11.18
 * Time: 16:38
 */

namespace OctaviusRocks\OQL;

class InvalidQueryException extends \Exception{
	public function __construct( $message = "", $code = 0, \Throwable $previous = NULL ) {
		parent::__construct( $message, $code, $previous );
	}
};