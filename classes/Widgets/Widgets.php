<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 09.05.17
 * Time: 09:26
 */

namespace OctaviusRocks\Widgets;

use OctaviusRocks\AdminNotice;
use OctaviusRocks\Plugin;

/**
 * @property Plugin plugin
 * @property LiveUsers live_users
 * @property Statistics statistics
 */
class Widgets {

	function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;

		$this->live_users = new LiveUsers( $this->plugin );
		$this->statistics = new Statistics( $this->plugin );

		add_action( 'wp_dashboard_setup', array( $this, 'setup' ) );
		add_action( 'widgets_init', [ $this, 'widgets_init' ] );
	}


	function setup() {

		try {
			$lastRun         = get_option( Plugin::OPTION_QUERY_CACHE_UPDATE_LAST_RUN, "" );
			$lastRunDateTime = new \DateTime( $lastRun );
			$diff            = $lastRunDateTime->diff( new \DateTime() );
			if ( $diff->h > 2 ) {
				$message = sprintf(
					__("Query cache cron job last execution was %d hours ago (%s). Please check!",	Plugin::DOMAIN),
					$diff->h,
					$lastRun
				);
				$this->plugin->admin_notices->enqueue(
					"<p>$message</p>",
					AdminNotice::TYPE_WARNING
				);
			}
		} catch ( \Exception $e ) {
			$message = __( "Query cache cron job seems not to be scheduled.", Plugin::DOMAIN );
			$this->plugin->admin_notices->enqueue(
				$message,
				AdminNotice::TYPE_WARNING
			);
		}

		if ( ! $this->plugin->config->has_valid_configuration() ) {
			$message = __( 'Dashboard Widgets are not available at the moment.', Plugin::DOMAIN );
			$this->plugin->admin_notices->enqueue( "<p>$message</p>" );

			return;
		}

		wp_add_dashboard_widget(
			LiveUsers::WIDGET_ID,
			__( 'Live User Rocks', Plugin::DOMAIN ),//Visible name for the widget
			array( $this->live_users, 'widget' ),      //Callback for the main widget content
			array( $this->live_users, 'config' )       //Optional callback for widget configuration content
		);

		wp_add_dashboard_widget(
			Statistics::WIDGET_ID,
			__( 'Statistics Rocks', Plugin::DOMAIN ),//Visible name for the widget
			array( $this->statistics, 'widget' ),      //Callback for the main widget content
			array( $this->statistics, 'config' )       //Optional callback for widget configuration content
		);

		$this->plugin->assets->enqueueAdminScript();

	}

	public function widgets_init() {
		MostReadWidget::register();
	}

}