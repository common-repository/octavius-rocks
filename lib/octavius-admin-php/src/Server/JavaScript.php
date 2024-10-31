<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 2019-02-06
 * Time: 13:59
 */

namespace OctaviusRocks\Server;


/**
 * @property ServerConfiguration serverConfiguration
 */
class JavaScript {

	/**
	 * JavaScript constructor.
	 *
	 * @param ServerConfiguration $serverConfiguration
	 */
	public function __construct($serverConfiguration) {
		$this->serverConfiguration = $serverConfiguration;
	}

	public function socketIO($minified = false){
		if($minified) return $this->getFileUrl("socket.io", true);
		return $this->serverConfiguration->getUrl("socket.io/socket.io.js");
	}

	public function core($minified = false){
		return $this->getFileUrl("core", $minified);
	}

	public function query($minified = false){
		return $this->getFileUrl("query", $minified);
	}

	public function tracker($minified = false){
		return $this->getFileUrl("tracker", $minified);
	}

	public function trackerClick($minified = false){
		return $this->getFileUrl("tracker-click", $minified);
	}

	public function trackerPageview($minified = false){
		return $this->getFileUrl("tracker-pageview", $minified);
	}
	public function trackerRendered($minified = false){
		return $this->getFileUrl("tracker-rendered", $minified);
	}


	/**
	 * @param string $file
	 * @param bool $minified
	 *
	 * @return string
	 */
	private function getFileUrl($file, $minified = false){
		return $this->serverConfiguration->getUrl(
			"files/". $this->getFilename($file, $minified)
		);
	}

	/**
	 * @param string $id
	 * @param bool $minified
	 *
	 * @return string
	 */
	private function getFilename($id, $minified = false){
		return "$id".(($minified)?".slim":"").".js";
	}



}