<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 23.05.17
 * Time: 10:25
 */

namespace OctaviusRocks;


class AdminNotice {

	const TYPE_ERROR = "notice-error";
	const TYPE_INFO = "notice-info";
	const TYPE_WARNING = "notice-warning";

	function __construct(Plugin $plugin) {
		$this->plugin = $plugin;
		$this->messages = array();
		$this->action_added = false;
	}

	/**
	 * enqueue message for admin notice
	 * @param string $message
	 * @param string $type
	 */
	public function enqueue($message, $type = self::TYPE_ERROR){
		if(!isset($this->messages[$type])){
			$this->messages[$type] = array();
		}
		$this->messages[$type][] = $message;
		$this->add_action();
	}

	/**
	 * add admin notice if not already done
	 */
	private function add_action(){
		if(!$this->action_added){
			$this->action_added = true;
			add_action( 'admin_notices', array($this, 'admin_notices') );
		}
	}

	/**
	 * display admin notices
	 */
	public function admin_notices(){

		foreach( $this->messages as $type => $messages ){
			foreach ($messages as $msg){
				$this->print_message($msg, $type);
			}
		}

	}

	/**
	 * @param string $message
	 * @param string $type
	 */
	public function print_message($message, $type = self::TYPE_ERROR){
		$class = 'notice '.$type;
		printf( '<div class="%1$s"><h2>Octavius Rocks</h2><div>%2$s</div></div>', $class, $message );
	}

}