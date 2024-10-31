<?php


namespace OctaviusRocks\Widgets;

use OctaviusRocks\Plugin;
use WP_Widget;

/**
 * @property Plugin plugin
 */
class MostReadWidget extends WP_Widget {

	const ID = "octavius_rocks_most_read";

	/**
	 * register this widget
	 */
	static function register(){
		register_widget(__NAMESPACE__."\MostReadWidget");
	}
	/**
	 * MostReadWidget constructor.
	 *
	 * @param Plugin $plugin
	 */
	public function __construct() {
		parent::__construct(
			self::ID,
			__("Most Read", Plugin::DOMAIN)
		);
	}

	/**
	 * frontend
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		$title = "";
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		}
		$title = apply_filters( 'widget_title', $instance['title'] );

		$utils = new Utils();
		$content = $utils->getContent();
		foreach ($instance as $key => $prop){
			if(property_exists($content, $key)) $content->{$key} = $instance[$key];
		}
		$utils->setContent($content);
		$result = $utils->fetch();

		$query = new \WP_Query(array(
			"post__in" => array_map(function($item){
				return $item["content_id"];
			},$result),
			"orderby" =>  "post__in",
			'post_type' => "any",
			"posts_per_page" => isset($instance["count"]) && !empty($instance["count"]) ? intval($instance["count"]) : Utils::DEFAULT_COUNT,
		));

		include Plugin::instance()->render->get_template_path(Plugin::TEMPLATE_WIDGET);

		wp_reset_postdata();

	}

	public function form( $instance ) {
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		}
		else {
			$title = __( 'Most Read', Plugin::DOMAIN );
		}
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
			<input
				class="widefat"
				id="<?php echo $this->get_field_id( 'title' ); ?>"
				name="<?php echo $this->get_field_name( 'title' ); ?>"
				type="text"
				value="<?php echo esc_attr( $title ); ?>"
			/>
		</p>
		<?php
		$utils = new Utils();
		$cs = $utils->contentStructure();
		foreach ($cs as $item){

			$type = $item["type"];
			$label = $item["label"];
			$key = $item["key"];

			// we dont need relations here
			if($key == "relation"){
				continue;
			}
			// we do not support taxonomy filter yet
			$taxonomy = $utils->getTaxonomy($key);
			if($taxonomy != null){
				continue;
			}

			$fieldId = $this->get_field_id($key);
			$fieldName = $this->get_field_name($key);



			$value = (isset($instance[$key])) ? $instance[$key]: $utils->content->{$key};


			echo "<p>";
			echo "<label for='$fieldId'>$label</label>";
			switch ($type){
				case "number":
					echo "<input class='widefat' id='$fieldId' name='$fieldName' type='number' value='$value' />";
					break;
				case "select":
					echo "<select class='widefat' id='$fieldId' name='$fieldName'>";
					foreach ($item["selections"] as $option){
						$optionKey = $option["key"];
						$text = $option["text"];
						$selected = ($value == $optionKey)? "selected":"";
						echo "<option $selected value='$optionKey'>$text</option>";
					}
					echo "</select>";
					break;
				default:
					echo "Unknown type: $type for key $key";
			}
			echo "</p>";
		}
	}

	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$utils = new Utils();
		foreach ($utils->contentStructure() as $item){
			$key = $item["key"];
			if(isset($new_instance[$key])) $instance[$key] = sanitize_text_field($new_instance[$key]);
		}
		return $instance;
	}


}
