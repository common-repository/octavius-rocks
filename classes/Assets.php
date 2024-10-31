<?php

namespace OctaviusRocks;

use OctaviusRocks\OQL\Condition;
use OctaviusRocks\OQL\ConditionSet;
use OctaviusRocks\OQL\Field;
use WP_Taxonomy;

/**
 * Class JS
 *
 * @property Plugin plugin
 * @property bool init_success
 * @property bool in_footer
 * @package OctaviusRocks
 */
class Assets {

	/**
	 * @var bool
	 */
	private $enqueue_admin_scripts;

	private $admin_info_extension;

	/**
	 * JS constructor.
	 *
	 * @param Plugin $plugin
	 */
	function __construct( Plugin $plugin ) {
		$this->plugin                = $plugin;
		$this->init_success          = false;
		$this->in_footer             = true;
		$this->enqueue_admin_scripts = false;
		$this->admin_info_extension = [];

		add_action( 'init', array( $this, 'init' ) );
		add_action( 'wp_enqueue_scripts', array(
			$this,
			'enqueue_scripts'
		) );
		add_action( 'admin_enqueue_scripts', array(
			$this,
			'admin_enqueue_scripts',
		) );
	}

    /**
     * set admin scripts to be enqueued
     * @param bool|array $extension
     */
	public function enqueueAdminScript($extension = false) {
		$this->enqueue_admin_scripts = true;
		if(is_array($extension)) $this->admin_info_extension = array_merge( $this->admin_info_extension, $extension);
	}

	public function getPostEditExtension(){
	    return [
	    	"oqlPageviews" => $this->plugin->gutenberg->getPostPageviewsOQL(),
            "oqlEvents" => $this->plugin->gutenberg->getPostEventsOQL(),
            "oqlTopReferer" => $this->plugin->gutenberg->getTopRefererPerPostOQL(),
            "post_id" => get_the_ID(),
        ];
    }

    /**
     * enqueue gutenberg javascript
     */
	public function enqueueGutenbergJS(){
	    $this->enqueueAdminScript($this->getPostEditExtension());
        $info = include $this->plugin->path."/dist/gutenbergRocks.asset.php";
        wp_enqueue_script(
            Plugin::HANDLE_JS_GUTENBERG,
            $this->plugin->url."/dist/gutenbergRocks.js",
            array_merge($info["dependencies"], [Plugin::HANDLE_JS_ADMIN]),
            $info["version"]
        );
    }

    public function enqueuePostMetaBoxJS(){
        $this->plugin->assets->enqueueAdminScript($this->getPostEditExtension());
    }

	/**
	 * register octavius scripts
	 */
	function init() {

		// ------------------------------------------------------------
		// core frontend script that has most of the functionality
		// ------------------------------------------------------------
		$info = include $this->plugin->path . "/dist/frontendRocks.asset.php";
		wp_register_script(
			Plugin::HANDLE_JS_FRONTEND,
			$this->plugin->url . "/dist/frontendRocks.js",
			$info["dependencies"],
			$info["version"],
			true
		);

		// ------------------------------------------------------------
		// every extension javascript especially those who use hooks
		// ------------------------------------------------------------
		$deps = apply_filters(Plugin::FILTER_REGISTER_FRONTEND_PLUGINS, [], Plugin::HANDLE_JS_FRONTEND);

		// ------------------------------------------------------------
		// very last frontend js that only initializes on dom ready
		// ------------------------------------------------------------
		wp_register_script(
			Plugin::HANDLE_JS_FRONTEND_LAST,
			$this->plugin->url . "/js/last-frontend-rocks.js",
			array_merge([Plugin::HANDLE_JS_FRONTEND],$deps),
			filemtime( $this->plugin->path . "/js/last-frontend-rocks.js" ),
			true
		);

		// ------------------------------------------------------------
		// admin area script
		// ------------------------------------------------------------
		$info = include $this->plugin->path . "/dist/adminRocks.asset.php";
		wp_register_script(
			Plugin::HANDLE_JS_ADMIN,
			$this->plugin->url . "/dist/adminRocks.js",
			$info["dependencies"],
			$info["version"],
			true
		);
		$this->init_success = true;
	}

	/**
	 * prevent loading octavius javascripts
	 *
	 * @param $doNotLoad
	 *
	 * @return boolean
	 */
	function doNotLoadScripts( $doNotLoad ) {
		return apply_filters(
			Plugin::FILTER_DO_NOT_LOAD_SCRIPTS,
			$doNotLoad
		);
	}

	/**
	 * enqueue octavius scripts
	 */
	function enqueue_scripts() {

		if (
			$this->doNotLoadScripts(
			is_preview() || is_customize_preview()
			)
		) {
			return;
		}

		if ( ! $this->init_success ) {
			return;
		}

		wp_enqueue_script( Plugin::HANDLE_JS_FRONTEND );
		wp_localize_script(
			Plugin::HANDLE_JS_FRONTEND,
			"WP_OctaviusRocks",
			array(
				"config" => $this->getConfig(),
				"autoPageview" => $this->plugin->pageview->skipPageview(Pageview::TYPE_JS) ? 0: 1,
				"autoClick" => !$this->plugin->config->is_click_tracking_disabled(),
				"autoRendered" => !$this->plugin->config->is_rendered_tracking_disabled(),
				"pageviewEntity" => $this->plugin->pageview->getEntity(),
			)
		);

		// enqueue extensions
		do_action( Plugin::ACTION_ENQUEUE_SCRIPTS, $this->in_footer, Plugin::HANDLE_JS_FRONTEND );

		// very last script please
		wp_enqueue_script( Plugin::HANDLE_JS_FRONTEND_LAST );
		wp_localize_script(
			Plugin::HANDLE_JS_FRONTEND_LAST,
			"WP_OctaviusRocks_Last",
			[
				"pixelUrl" => $this->plugin->pageview->get_pixel_url()
			]
		);


	}

	function admin_enqueue_scripts() {

		if ( $this->doNotLoadScripts(
			! $this->enqueue_admin_scripts || is_preview() || is_customize_preview()
		) ) {
			return;
		}

		wp_enqueue_script(Plugin::HANDLE_JS_ADMIN);
		$admin = [
			"domain" => Plugin::DOMAIN,
			"config" => $this->getConfig(),
			"rest" => [
				"GET_taxonomy_term_views" => $this->plugin->rest->getTaxonomyTermViewsRoute(),
			],
			"breakpointClusters" => $this->plugin->widgets->live_users->getConfig(),
			"taxonomy_term_views" => [
				"first_period" => $this->plugin->taxonomyTermViews->getFirstPeriod(),
				"taxonomies" => array_values(
					array_filter(
						array_map(
							function($tax){
								$taxonomy = get_taxonomy($tax);
								if(!($taxonomy instanceof WP_Taxonomy)) return null;
								if($taxonomy->name == null || $taxonomy->label == null || $taxonomy->labels == null) return null;
								return [
									"name" => $taxonomy->name,
									"label" => $taxonomy->label,
									"singular" => $taxonomy->labels->singular_name,
								];
							},
							$this->plugin->taxonomyTermViews->getTaxonomies()
						),
						function($item){
							return is_array($item);
						}
					)
				),
			],
			"extensions" => $this->admin_info_extension,
			"i18n" => [
				"base"=>[
					"hits" => __("Hits", Plugin::DOMAIN),
					"unknown" => __("Unknown", Plugin::DOMAIN),
				],
				"app" => [
					"nav_events" => _x("Events", "Navigation", Plugin::DOMAIN),
					"nav_posts" => _x("Posts", "Navigation", Plugin::DOMAIN),
					"nav_topics" => _x("Topics", "Navigation", Plugin::DOMAIN),
					"nav_pageviews" => _x("Pageviews", "Dashboard navigation", Plugin::DOMAIN),
					"nav_top_posts" => _x("Top Posts", "Dashboard navigation", Plugin::DOMAIN),
					"nav_top_referer" => _x("Top Referer", "Dashboard navigation", Plugin::DOMAIN),
				],
				"breakpointsConfig" => [
					// live breakpoints config
					"button_delete" => _x("Delete", "breakpointsConfig", Plugin::DOMAIN),
					"button_add" => _x("Add new", "breakpointsConfig", Plugin::DOMAIN),
					"description" => _x("Define a list of breakpoint clusters. Those will be clustered in the realtime breakpoints view.", "breakpointsConfig", Plugin::DOMAIN),
					"label_key" => _x("Label", "breakpointsConfig", Plugin::DOMAIN),
					"label_min_width" => _x("Min. width", "breakpointsConfig", Plugin::DOMAIN),
					"label_max_width" => _x("Max. width", "breakpointsConfig", Plugin::DOMAIN),
				],
				"dashboard" => [
					"from" => __("From", Plugin::DOMAIN),
					"to" => __("To", Plugin::DOMAIN),
					"minutes_5" => __("5 min.", Plugin::DOMAIN),
					"hours" => __("Hours", Plugin::DOMAIN),
					"days" => __("Days", Plugin::DOMAIN),
					"months" => __("Months", Plugin::DOMAIN),
					"condition_label_and" => _x("And", "Dashboard", Plugin::DOMAIN),
					"condition_label_or" => _x("Or", "Dashboard", Plugin::DOMAIN),
					"top_posts" => __("Top Posts", Plugin::DOMAIN),
					"top_referer" => __("Top Referer", Plugin::DOMAIN),
					"screen_sizes" => __("Screen sizes", Plugin::DOMAIN),
					"label_dates" => _x("Time period", "Pageviews page", Plugin::DOMAIN),
					"label_between_dates" => _x("to", "Pageviews page", Plugin::DOMAIN),
					"label_aggregation" => _x("Group by", "Pageviews page", Plugin::DOMAIN),
					"aggregation_day" => _x("Day", "Pageviews page", Plugin::DOMAIN),
					"aggregation_month" => _x("Month", "Pageviews page", Plugin::DOMAIN),
					"label_content_type" => _x("Content type", "Pageviews page", Plugin::DOMAIN),
					"button_submit" => _x("Query", "Pageviews page", Plugin::DOMAIN),
					"button_reset" => _x("Reset", "Pageviews page", Plugin::DOMAIN),
				],
				"taxonomy_term_views" => [
					"all_taxonomies" => __("All taxonomies", Plugin::DOMAIN),
					"load_more" => __("Load more", Plugin::DOMAIN),
				],
				"referer"=>[
					"direct_call" => __("Direct call", Plugin::DOMAIN),
				],
				"healthCheck" => [
					"websocket_connected" => _x("✅ Is connected to service", "healthcheck", Plugin::DOMAIN),
					"websocket_not_connected" =>  _x("🚨 Is not connected to service", "healthcheck", Plugin::DOMAIN),
					"get_events" =>  _x("✅ %s events counted", "healthcheck", Plugin::DOMAIN),
					"no_events" =>  _x("🚨 Could not fetch data", "healthcheck", Plugin::DOMAIN),
				],
				"weekdays" => [
					__("Su", Plugin::DOMAIN),
					__("Mo", Plugin::DOMAIN),
					__("Tu", Plugin::DOMAIN),
					__("We", Plugin::DOMAIN),
					__("Th", Plugin::DOMAIN),
					__("Fr", Plugin::DOMAIN),
					__("Sa", Plugin::DOMAIN),
				],
				"months" => [
					__("Jan", Plugin::DOMAIN),
					__("Feb", Plugin::DOMAIN),
					__("Mar", Plugin::DOMAIN),
					__("Apr", Plugin::DOMAIN),
					__("May", Plugin::DOMAIN),
					__("Jun", Plugin::DOMAIN),
					__("Jul", Plugin::DOMAIN),
					__("Aug", Plugin::DOMAIN),
					__("Sep", Plugin::DOMAIN),
					__("Oct", Plugin::DOMAIN),
					__("Nov", Plugin::DOMAIN),
					__("Dec", Plugin::DOMAIN),
				],
			]
		];
		wp_localize_script(
			Plugin::HANDLE_JS_ADMIN,
			"WP_OctaviusRocks_Admin",
			$admin
		);



		// enqueue extensions
		do_action( Plugin::ACTION_ENQUEUE_ADMIN_SCRIPTS, $this->in_footer, Plugin::HANDLE_JS_ADMIN );

	}

	/**
	 * @return array
	 */
	function getConfig() {
		$config = array(
			"serviceDomain"   => "service.octavius.rocks",
			"serviceUseHttps" => true,
			"apiKey"          => $this->plugin->config->get_api_key(),
			"server"          => $this->plugin->config->get_server(),
			"path"            => $this->plugin->config->get_server_path(),
			"useHttps"        => $this->plugin->config->use_ssl(),
		);

		if ( is_admin() ) {
			$config["clientSecret"] = $this->plugin->config->get_client_secret();
		}

		return $config;
	}


}
