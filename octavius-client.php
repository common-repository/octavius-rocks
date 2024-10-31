<?php

namespace OctaviusRocks;

use OctaviusRocks\Client\Client;
use OctaviusRocks\Database\Pageviews;
use OctaviusRocks\Database\Queries;
use OctaviusRocks\Database\TaxonomyTermViews;
use OctaviusRocks\Model\CacheConfig;
use OctaviusRocks\Widgets\Widgets;

/**
 * Plugin Name:       Octavius Rocks
 * Plugin URI:        http://www.octavius.rocks
 * Description:       Track user behaviour on your website. View the data live on the dashboard.
 * Version:           3.9.3
 * Requires at least: 5.0
 * Tested up to:      6.2.0
 * Author:            PALASTHOTEL by Edward and Julia
 * Author URI:        http://www.palasthotel.de
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       octavius-rocks
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

require_once dirname(__FILE__)."/vendor/autoload.php";

/**
 * @property string path_parts
 * @property ServerConfigurationStore config
 * @property Update update
 * @property PageInfo page_info
 * @property Tracker tracker
 * @property Settings settings
 * @property AdminNotice admin_notices
 * @property MetaBox $meta_box
 * @property Widgets widgets
 * @property Schedule schedule
 * @property Assets $assets
 * @property Pageview pageview
 * @property Render render
 * @property Grid grid
 * @property AdminMenu admin_menu
 * @property PostsTable postsTable
 * @property Pageviews $pageviews
 * @property PageviewCache pageviewCache
 * @property PageviewsWPQuery pageviewWPQuery
 * @property Amp amp
 * @property Gutenberg gutenberg
 * @property BlockX blockx
 * @property Queries queries
 * @property QueryCache queryCache
 * @property Repository repo
 * @property TaxonomyTermViews $taxonomyTermViews
 * @property REST $rest
 * @property Compat $compat
 */
class Plugin extends Components\Plugin {

	const DOMAIN = 'octavius-rocks';

	/*
	 * directory and file paths
	 */
	const THEME_FOLDER = "plugin-parts";
	const TEMPLATE_WIDGET = "most-read-widget.php";

	/*
	 * handles
	 */
	const HANDLE_JS_ADMIN = "octavius-rocks-admin";
	const HANDLE_CSS_ADMIN = "octavius-rocks-css-pageview";
	const HANDLE_JS_GUTENBERG = "octavius-rocks-gutenberg";
	const HANDLE_JS_FRONTEND = "octavius-rocks-frontend";
	const HANDLE_JS_FRONTEND_LAST = "octavius-rocks-frontend-last";

	/*
	 * Actions
	 */
	const ACTION_ENQUEUE_SCRIPTS = "octavius_rocks_enqueue_scripts";
	const ACTION_ENQUEUE_ADMIN_SCRIPTS = "octavius_rocks_enqueue_admin_scripts";
	const ACTION_GRID_LOAD_CLASSES = "octavius_rocks_grid_load_classes";

	const ACTION_RENDER_JS = "octavius_rocks_render_js";
	const ACTION_RENDER_ATTRIBUTES = "octavius_rocks_render_attributes";
	const ACTION_RENDER_TEASER_ATTRIBUTES = "octavius_rocks_render_teaser_attributes";
	const ACTION_RENDER_SETTINGS = "octavius_rocks_render_settings_{{type}}";

	const ACTION_PAGE_INFO = "octavius_rocks_page_info";

	const ACTION_ADMIN_SUBMENU = "octavius_rocks_admin_submenu";
	const ACTION_MODIFY_PAGEVIEWS_CACHE_ARGUMENTS = "octavius_rocks_modify_pageviews_cache_arguments";

	const ACTION_POSTS_TABLE_PAGE_VIEWS_COL = "octavius_rocks_posts_table_page_views_column";

	/**
	 * filters
	 */
	const FILTER_REGISTER_FRONTEND_PLUGINS = "octavius_rocks_register_frontend_plugins";
	const FILTER_DO_NOT_LOAD_SCRIPTS = "octavius_rocks_do_not_load_scripts";
	const FILTER_SKIP_PAGEVIEW = "octavius_rocks_skip_pageview";
	const FILTER_PAGEVIEW_ENTITY = "octavius_rocks_pageview_entity";
	const FILTER_ADMIN_OVERVIEW_LINKS = "octavius_rocks_admin_overview_links";
	const FILTER_CONFIGURATION_ENDPOINT = "octavius_rocks_configuration_endpoint";
	const FILTER_ATTRIBUTES = "octavius_rocks_attributes";
	const FILTER_TEASER_ATTRIBUTES = "octavius_rocks_teaser_attributes";
	const FILTER_OCTAVIUS_QUERY_TIMEOUT = "octavius_rocks_query_timeout";
	const FILTER_TEMPLATES = "octavius_rocks_templates";
	const FILTER_GRID_VIEWMODES = "octavius_rocks_grid_post_viewmodes";
	const FILTER_GRID_BOX_SHOW_TAXONOMY = "octavius_rocks_grid_box_show_taxonomy";
	const FILTER_GRID_BOX_SHOW_POST_TYPE = "octavius_rocks_grid_box_show_post_type";
	const FILTER_POSTS_TABLE_ARGUMENTS = "octavius_rocks_posts_table_arguments";
	const FILTER_QUERY_ATTRIBUTES_SKIP_TAXONOMY = "octavius_rocks_query_attributes_skip_taxonomy";
	const FILTER_DASHBOARD_CAPABILITY = "octavius_rocks_dashboard_capability";

	const FILTER_API_RESPONSE_GET_CONTENTS_ITEM = "octavius_rocks_api_get_contents_item";
	const FILTER_POSTS_TABLE_PAGE_VIEWS = "octavius_rocks_posts_table_page_views";
	const FILTER_POSTS_TABLE_POSTS_GROUP = "octavius_rocks_posts_table_posts_group";
	const FILTER_DEFAULT_CACHE_CONFIG = "octavius_rocks_default_cache_config";

	/**
	 * option keys
	 */
	const OPTION_API_KEY = "_octavius_rocks_api_key";
	const OPTION_SERVER = "_octavius_rocks_server";
	const OPTION_SERVER_PATH = "_octavius_rocks_server_path";
	const OPTION_API_VERSION = "_octavius_rocks_api_version_readable";
	const OPTION_API_VERSION_READABLE = "_octavius_rocks_api_version_readable";
	const OPTION_FETCH_CONFIG_RESULT = "_octavius_rocks_fetch_config_result";
	const OPTION_FETCH_CONFIG_LAST_RUN = "_octavius_rocks_fetch_config_last_run";
	const OPTION_FETCH_CONFIG_LAST_RUN_SUCCESS = "_octavius_rocks_fetch_config_last_run_success";
	const OPTION_CLIENT_SECRET = "_octavius_rocks_client_secret";
	const OPTION_DISABLE_TRACK_CLICKS = "_octavius_rocks_disable_track_clicks";
	const OPTION_DISABLE_TRACK_RENDERED = "_octavius_rocks_disable_track_rendered";
	const OPTION_DISABLE_TRACK_PIXEL = "_octavius_rocks_disable_track_pixel";

	const OPTION_BREAKPOINTS_CSS = "octavius_rocks_breakpoints_css";

	const OPTION_QUERY_CACHE_UPDATE_LAST_RUN = "octavius_rocks_query_cache_update_last_run";

	/**
	 * transients
	 */
	const TRANSIENT_SCHEDULE_QUERY_CACHE_LOCK = "_octavius_rocks_fetchQueries_running";

	/**
	 * schedules
	 */
	const SCHEDULE_FETCH_CLIENT_CONFIG = "octavius_rocks_schedule_fetch_client_config";
	const SCHEDULE_FETCH_PAGEVIEWS = "octavius_rocks_schedule_fetch_pageviews";
	const SCHEDULE_FETCH_TAXONOMY_TERM_VIEWS = "octavius_rocks_schedule_fetch_taxonomy_term_views";
	const SCHEDULE_FETCH_QUERY_CACHE = "octavius_rocks_schedule_fetch_query_cache";

	/**
	 * OctaviusRocks constructor.
	 */
	function onCreate() {

		$this->loadTextdomain(
			self::DOMAIN,
			"languages"
		);

		/*
		 * paths
		 */
		$this->path_parts = $this->path . '/parts';

		// add octavius library if not exists
		if ( ! class_exists( "OctaviusRocks\Server\Request" ) ) {
			require_once dirname( __FILE__ ) . '/lib/octavius-admin-php/autoload.php';
		}

		$this->pageviews       = new Pageviews();
		$this->pageviewCache   = new PageviewCache($this);
		$this->pageviewWPQuery = new PageviewsWPQuery($this);
		$this->taxonomyTermViews = new TaxonomyTermViews();

		$this->rest = new REST($this);

		$this->queries = new Queries();

		$this->repo = new Repository($this);

		// wp cli
		if(class_exists("\WP_CLI")){
			\WP_CLI::add_command('octavius-rocks', new CLI($this));
		}

		$this->config = ServerConfigurationStore::instance();
		$this->assets = new Assets( $this );
		$this->render = new Render($this);
		$this->update = new Update( $this );
		$this->page_info = new PageInfo( $this );
		$this->admin_menu = new AdminMenu( $this );
		$this->settings = new Settings( $this );

		$this->tracker = new Tracker( $this );
		$this->pageview = new Pageview( $this );
		$this->schedule = new Schedule( $this );
		$this->widgets = new Widgets( $this );
		$this->meta_box = new MetaBox( $this );
        $this->gutenberg = new Gutenberg($this);
		$this->postsTable = new PostsTable($this);
		$this->admin_notices = new AdminNotice( $this );
		$this->grid = new Grid( $this );
		$this->blockx = new BlockX($this);
		$this->amp = new Amp($this);

		$this->compat = new Compat($this);

		if(WP_DEBUG){
			$this->onSiteActivation();
		}

	}

	public function onSiteActivation() {
		parent::onSiteActivation();
		$this->pageviews->createTables();
		$this->queries->createTables();
		$this->taxonomyTermViews->createTables();
		$this->schedule->manage();
	}

	public function onSiteDeactivation() {
		parent::onSiteDeactivation();
		$this->schedule->unmanage();
	}

	/**
	 * Get Client Object
	 *
	 * @return Client | false
	 */

	private $client;

	/**
	 * @return bool|Client
	 */
	function getClient() {

		if ( $this->client === null ) {

			$connection = ServerConfigurationStore::instance()->connect();
			if ( $connection === null ) {
				$this->client = false;
			} else {
				$this->client = new Client( ServerConfigurationStore::instance()->get_api_key(), $connection );
			}
		}

		return $this->client;
	}

	function getDefaultCacheConfig(string $id): CacheConfig {
		return apply_filters( Plugin::FILTER_DEFAULT_CACHE_CONFIG, CacheConfig::build($id));
	}

	/**
	 * @deprecated use Plugin::instance() instead
	 */
	public static function get_instance(){
		return self::instance();
	}

}

Plugin::instance();

require_once dirname( __FILE__ ) . "/public-functions.php";
