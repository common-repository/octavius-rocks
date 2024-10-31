<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 04.05.17
 * Time: 12:26
 */

namespace OctaviusRocks;


/**
 * @property Plugin plugin
 */
class Pageview {

	const TYPE_JS = "js";
	const TYPE_PIXEL = "pixel";

	function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;

		/**
		 * octavius pixel in footer
		 */
		add_action( 'wp_footer', array( $this, 'render_pixel' ), 100 );

	}

	/**
	 * @param $type
	 *
	 * @return boolean
	 */
	public function skipPageview($type){
		return apply_filters(
				Plugin::FILTER_SKIP_PAGEVIEW,
				is_preview() ||is_customize_preview(),
				$type
		) === true;
	}

	/**
	 * get pageview entity for running request
	 * @return array
	 */
	function getEntity(){
		return apply_filters(Plugin::FILTER_PAGEVIEW_ENTITY, $this->plugin->page_info->asEntity());
	}

	/**
	 * @return string
	 */
	public function get_pixel_url(){

		$entity = $this->getEntity();
		$entity["url"] = $entity["content_url"];
		unset($entity["content_url"]);

		$args = http_build_query($entity);

		$service_url = ($this->plugin->config->use_ssl()? "https://": "http://").$this->plugin->config->get_server().$this->plugin->config->get_server_path();
		$service_url .= "send/" . $this->plugin->config->get_api_key()."?$args";
		return $service_url;
	}


	/**
	 * render scripts for tracking
	 */
	public function render_pixel() {

		if($this->plugin->config->is_pixel_tracking_disabled()) return false;
		if($this->skipPageview(self::TYPE_PIXEL)) return false;

		$url = $this->get_pixel_url();

		?>
		<img
		style="height: 0px;width: 0px;overflow: hidden;position: absolute;bottom: 0;left: 0;z-index: -10;"
		src="<?php echo esc_url($url); ?>"
		/>
		<?php
	}


}