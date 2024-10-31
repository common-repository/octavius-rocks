<?php


namespace OctaviusRocks;


/**
 * @property Plugin plugin
 */
class Amp {

	/**
	 * Amp constructor.
	 *
	 * @param Plugin $plugin
	 */
	public function __construct($plugin) {
		$this->plugin = $plugin;
		add_action('amp_post_template_footer', array($this, 'amp_post_template_footer'));
	}

	public function amp_post_template_footer(){
		$this->plugin->page_info->viewmode = "amp";
		$url = $this->plugin->pageview->get_pixel_url();
		?>
		<amp-pixel
			src="<?php echo esc_url($url); ?>"
			layout="nodisplay"
		></amp-pixel>
		<?php
	}
}
