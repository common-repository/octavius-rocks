<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 12.11.18
 * Time: 19:11
 */

namespace OctaviusRocks\Server;


class ServerConfiguration {

	/**
	 * @var string
	 */
	private $server;

	/**
	 * @var string
	 */
	private $versionPath;

	/**
	 * @var int
	 */
	private $version;

	/**
	 * @var string
	 */
	private $version_name;

	/**
	 * @var bool
	 */
	private $use_https;

	/**
	 * ServerConfiguration constructor.
	 *
	 * @param string $server
	 * @param string $versionPath
	 * @param bool $use_https
	 * @param int $version
	 * @param string $version_name
	 */
	public function __construct(
		$server,
		$versionPath,
		$use_https = true,
		$version = 0,
		$version_name = ""
	) {
		$this->server       = $server;
		$this->versionPath  = $versionPath;
		$this->version      = $version;
		$this->version_name = $version_name;
		$this->use_https    = $use_https;
	}

	/**
	 * @param string $server
	 * @param string $versionPath
	 * @param bool $use_https
	 *
	 * @return ServerConfiguration
	 */
	public static function builder( $server, $versionPath, $use_https = true ) {
		return new ServerConfiguration( $server, $versionPath, $use_https );
	}

	/**
	 * @return string
	 */
	public function getServer() {
		return $this->server;
	}

	/**
	 * @return int
	 */
	public function getVersion() {
		return $this->version;
	}

	/**
	 * @return string
	 */
	public function getVersionName() {
		return $this->version_name;
	}

	/**
	 * @return string
	 */
	public function getVersionPath() {
		return $this->versionPath;
	}

	/**
	 * get full url path for call
	 *
	 * @param $path
	 *
	 * @return string
	 */
	public function getUrl( $path ) {
		$https = ( $this->use_https ) ? "s" : "";

		return "http$https://{$this->server}{$this->versionPath}$path";
	}

	/**
	 * @return array
	 */
	public function get() {
		return array(
			"server"   => $this->server,
			"path"     => $this->versionPath,
			"version"  => array(
				"id"       => $this->version,
				"readable" => $this->version_name,
			),
			"useHttps" => $this->use_https,
		);
	}

	/**
	 * @param array $json
	 *
	 * @return ServerConfiguration
	 */
	static public function parse( $json ) {
		$version      = $json["version"]["id"];
		$version_name = $json["version"]["readable"];
		$use_https    = $json["useHttps"];

		return new ServerConfiguration(
			$json["server"],
			$json["path"],
			$use_https,
			$version,
			$version_name
		);
	}
}