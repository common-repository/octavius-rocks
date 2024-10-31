<?php

namespace OctaviusRocks;

use OctaviusRocks\Model\QueryCacheResponse;
use OctaviusRocks\Server\RequestConfiguration;
use OctaviusRocks\Server\ServerConfigurationHandler;

/**
 * @property Plugin plugin
 * @property string[] schedules
 */
class Schedule {

	/**
	 * @var \CronLogger\Plugin|null
	 */
	private $logger;

	/**
	 * Schedule constructor.
	 *
	 * @param Plugin $plugin
	 */
	function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;

		add_action( 'admin_init', array( $this, 'manage' ) );

		$this->schedules = array(
			Plugin::SCHEDULE_FETCH_CLIENT_CONFIG,
			Plugin::SCHEDULE_FETCH_PAGEVIEWS,
			Plugin::SCHEDULE_FETCH_TAXONOMY_TERM_VIEWS,
			Plugin::SCHEDULE_FETCH_QUERY_CACHE,
		);

		add_action( Plugin::SCHEDULE_FETCH_CLIENT_CONFIG, array( $this, "fetch_client_config" ) );
		add_action( Plugin::SCHEDULE_FETCH_PAGEVIEWS, array($this, "fetch_pageviews"));
		add_action( Plugin::SCHEDULE_FETCH_TAXONOMY_TERM_VIEWS, array($this, "fetch_taxonomy_term_views"));
		add_action( Plugin::SCHEDULE_FETCH_QUERY_CACHE, array($this, "fetch_query_cache"));

		// if CronLogger Plugin is activated
		$this->logger = null;
		add_action("cron_logger_init", function($logger){
			$this->logger = $logger;
		});
	}

	public function isFetchPageviewsDisabled(): bool {
		return defined('OCTAVIUS_ROCKS_CRON_FETCH_PAGEVIEW_DISABLED') && OCTAVIUS_ROCKS_CRON_FETCH_PAGEVIEW_DISABLED === true;
	}

	public function isFetchTaxonomyTermViewsDisabled(): bool {
		return defined('OCTAVIUS_ROCKS_CRON_FETCH_TAXONOMY_TERM_VIEWS_DISABLED') && OCTAVIUS_ROCKS_CRON_FETCH_TAXONOMY_TERM_VIEWS_DISABLED === true;
	}

	/**
	 * @return false|int
	 */
	public function isClientConfigScheduled() {
		return $this->isScheduled( Plugin::SCHEDULE_FETCH_CLIENT_CONFIG );
	}

	/**
	 * @param string $action one of the schedule actions
	 *
	 * @return false|int
	 */
	public function isScheduled($action){
		return wp_next_scheduled( $action );
	}

	/**
	 * start scheduled event
	 */
	public function manage() {

		foreach ($this->schedules as $action){

			if($action === Plugin::SCHEDULE_FETCH_PAGEVIEWS) continue;
			if($action === Plugin::SCHEDULE_FETCH_TAXONOMY_TERM_VIEWS) continue;

			if(!$this->isScheduled($action)){
				wp_schedule_event( time(), 'hourly', $action );
			}
		}

		if(!$this->isScheduled(Plugin::SCHEDULE_FETCH_PAGEVIEWS)){
			if(!$this->isFetchPageviewsDisabled()){
				wp_schedule_event( time(), 'hourly', Plugin::SCHEDULE_FETCH_PAGEVIEWS );
			}
		} else if( $this->isFetchPageviewsDisabled() ){
			wp_clear_scheduled_hook( Plugin::SCHEDULE_FETCH_PAGEVIEWS );
		}

		if(!$this->isScheduled(Plugin::SCHEDULE_FETCH_TAXONOMY_TERM_VIEWS)){
			if(!$this->isFetchTaxonomyTermViewsDisabled()){
				wp_schedule_event( time(), 'twicedaily', Plugin::SCHEDULE_FETCH_TAXONOMY_TERM_VIEWS );
			}
		} else if( $this->isFetchTaxonomyTermViewsDisabled() ){
			wp_clear_scheduled_hook( Plugin::SCHEDULE_FETCH_TAXONOMY_TERM_VIEWS );
		}
	}

	/**
	 * stop scheduled action
	 */
	public function unmanage() {
		foreach ($this->schedules as $action){
			wp_clear_scheduled_hook( $action);
		}
	}

	/**
	 * fetch octavius server configuration for client
	 */
	public function fetch_client_config() {
		ServerConfigurationHandler::fetch_server_configuration();
	}

	/**
	 * fetch new pageviews since last fetch
	 */
	public function fetch_pageviews(){

		if($this->isFetchPageviewsDisabled()){
			error_log("OctaviusRocks fetch pageviews schedule is active but disabled.");
			return;
		}

		RequestConfiguration::setGlobalTimeout(5*MINUTE_IN_SECONDS);
		$limit = 100;
		$page = 1;
		$imported = 0;
		$skipped = 0;
		$start = time();
		while($result = $this->plugin->pageviewCache->importPageviewsUpdate($limit,$page++)){
			$imported+= $result;
			$calculated = $page*$limit;
			$skipped  = $calculated - $imported;
		}
		$this->plugin->pageviewCache->updateLastImport();
		if($this->logger != null){
			$this->logger->log->addInfo(
				"Imported $imported; Skipped $skipped",
				time()-$start
			);
		}
	}

	public function fetch_taxonomy_term_views(){

		if($this->isFetchTaxonomyTermViewsDisabled()){
			error_log("OctaviusRocks fetch taxonomy term views schedule is active but disabled.");
			return;
		}

		$taxonomies = get_taxonomies([
			"public" => true,
		]);
		foreach ($taxonomies as $taxonomy){
			$terms = get_terms([
				"taxonomy" => $taxonomy,
				"hide_empty" => true,
			]);
			$start = time();
			foreach ($terms as $term){
				$this->plugin->repo->fetchTaxonomyTermViews($term);
			}

			if($this->logger != null){
				$count = count($terms);
				$this->logger->log->addInfo(
					"Fetched views for $count terms",
					time()-$start
				);
			}
		}
	}

	/**
	 * @return bool
	 */
	public static function isRunningFetchQueryCache(): bool {
		return true == get_transient(Plugin::TRANSIENT_SCHEDULE_QUERY_CACHE_LOCK);
	}

	/**
	 * @param bool $isRunning
	 */
	public static function setIsRunningFetchQueryCache($isRunning){
		if($isRunning){
			set_transient(
				Plugin::TRANSIENT_SCHEDULE_QUERY_CACHE_LOCK,
				$isRunning,
				 DAY_IN_SECONDS // just in case something broke the process
			);
		} else {
			delete_transient(Plugin::TRANSIENT_SCHEDULE_QUERY_CACHE_LOCK);
		}
	}

	/**
	 * fetch query cache update
	 */
	public function fetch_query_cache(){

		RequestConfiguration::setGlobalTimeout(5 * MINUTE_IN_SECONDS);

		if( self::isRunningFetchQueryCache() ) {
			return;
		}
		self::setIsRunningFetchQueryCache(true);

		$plugin = $this->plugin;
		$lastRun = get_option(Plugin::OPTION_QUERY_CACHE_UPDATE_LAST_RUN, "");

		if( !empty($lastRun)){
			$results = $plugin->queries->getAll($lastRun);
		} else {
			$results = $plugin->queries->getAll();
		}

		$plugin->repo->updateQueryCache($results);

		update_option(Plugin::OPTION_QUERY_CACHE_UPDATE_LAST_RUN, date("Y-m-d H:i:s"));

		self::setIsRunningFetchQueryCache(false);

	}

}
