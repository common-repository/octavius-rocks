<?php


namespace OctaviusRocks;


use OctaviusRocks\Server\RequestConfiguration;
use OctaviusRocks\Server\ServerConfigurationHandler;

/**
 * @property Plugin plugin
 */
class CLI {

	/**
	 * OctaviusRocksCommand constructor.
	 *
	 * @param Plugin $plugin
	 */
	public function __construct(Plugin $plugin) {
		$this->plugin = $plugin;
	}

	/**
	 * fetch config from service
	 *
	 * ## OPTIONS
	 *
	 * [--type=<type>]
	 * : what do you want to fetch?
	 * ---
	 * default: config
	 * options:
	 *   - config
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 *   wp octavius-rocks fetch --type=config
	 *
	 * @when after_wp_load
	 */
	public function fetch($args, $assoc_args){
		switch ($assoc_args["type"]){
			case "config":
				ServerConfigurationHandler::fetch_server_configuration();
				break;
		}
		\WP_CLI::success("Fetched ".$assoc_args["type"]);
	}

	/**
	 * Imports pageviews to cache
	 *
	 * ## OPTIONS
	 *
	 * [--type=<type>]
	 * : type of import operation
	 * ---
	 * default: full
	 * options:
	 *   - full
	 *   - update
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 *     wp octavius-rocks import
	 *
	 * @when after_wp_load
	 */
	public function import($args, $assoc_args){
		// set global request timeout to 24 hours for cli requests
		RequestConfiguration::setGlobalTimeout(24*60*60);
		$limit = 1000;
		$page = 1;
		$imported = 0;
		$type = $assoc_args["type"];
		switch ($type){
			case "update":
				\WP_CLI::line("Update since ".$this->plugin->pageviewCache->getLastImport());
				while($result = $this->plugin->pageviewCache->importPageviewsUpdate($limit,$page++)){
					$imported+= $result;
					$calculated = $page*$limit;
					$skipped  = $calculated - $imported;
					\WP_CLI::line("Imported $imported pageviews; Skipped $skipped");
				}
				break;
			default:
				\WP_CLI::line("Overwrite all pageviews");
				while($result = $this->plugin->pageviewCache->importPageviews($limit,$page++)){
					$imported+= $result;
					$calculated = $page*$limit;
					$skipped  = $calculated - $imported;
					\WP_CLI::line("Imported $imported pageviews; Skipped $skipped");
				}

				break;
		}
		$this->plugin->pageviewCache->updateLastImport();
		\WP_CLI::success("Import done!");
	}

	/**
	 * Fetches cached queries
	 *
	 * ## OPTIONS
	 *
	 * [--scope=<type>]
	 * : scope of operation
	 * ---
	 * default: all
	 * options:
	 *   - all
	 *   - update
	 * ---
	 *
	 * [--force=<boolean>]
	 * : ignores running lock if true
	 * ---
	 * default: false
	 * options:
	 *   - true
	 *   - false
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 *     wp octavius-rocks fetchQueries --scope=all
	 *
	 * @when after_wp_load
	 */
	public function fetchQueries($args, $assoc_args){

		if( Schedule::isRunningFetchQueryCache() && $assoc_args["force"] !== "true") {
			\WP_CLI::error("Fetch Queries is already running");
			return;
		}
		Schedule::setIsRunningFetchQueryCache(true);

		$type = $assoc_args["scope"];
		$plugin = Plugin::instance();

		$lastRun = get_option(Plugin::OPTION_QUERY_CACHE_UPDATE_LAST_RUN, "");

		if("update" == $type && !empty($lastRun)){
			\WP_CLI::line("Update queries since last run $lastRun");
			$results = $plugin->queries->getAll($lastRun);
		} else {
			\WP_CLI::line("Update all queries");
			$results = $plugin->queries->getAll();
		}

		\WP_CLI::line(count($results)." queries will be updated...");

		$plugin->repo->updateQueryCache($results);

		update_option(Plugin::OPTION_QUERY_CACHE_UPDATE_LAST_RUN, date("Y-m-d H:i:s"));

		Schedule::setIsRunningFetchQueryCache(false);
		\WP_CLI::success("Update done!");

	}

	/**
	 * Fetches taxonomy term views
	 *
	 * ## OPTIONS
	 *
	 * [--taxonomy=<taxonomy>]
	 * : choose taxonomy
	 * ---
	 * default: false
	 *
	 * ---
	 *
	 * [--days=<days>]
	 * : ignores running lock if true
	 * ---
	 * default: 2
	 *
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 *     wp octavius-rocks fetchTaxonomyTermViews --taxonomy=category --days=2
	 *
	 * @when after_wp_load
	 */
	public function fetchTaxonomyTermViews($args, $assoc_args){
		$taxonomy = $assoc_args["taxonomy"];
		$days = $assoc_args["days"];
		$taxonomies=[];
		if($taxonomy){
			$taxonomies[] = $taxonomy;
		} else {
			$taxonomies = get_taxonomies([
				"public" => true,
			]);
		}

		foreach ($taxonomies as $taxonomy){
			$terms = get_terms([
				"taxonomy" => $taxonomy,
				"hide_empty" => true,
			]);
			$count = count($terms);
			$progress = \WP_CLI\Utils\make_progress_bar( "'$taxonomy' with $count terms", $count );
			$start = time();
			foreach ($terms as $term){
				$this->plugin->repo->fetchTaxonomyTermViews($term, $days);
				$progress->tick();
			}
			$progress->finish();
			$duration = time()-$start;
			\WP_CLI::line("Time needed: $duration seconds.");

		}
	}

	/**
	 * Clears pageviews cache
	 *
	 * ## EXAMPLES
	 *
	 *     wp octavius-rocks clear
	 */
	public function clear(){
		\WP_CLI::line("All cached pageviews will be deleted...4 seconds to cancel this execution");
		sleep(4);
		$this->plugin->pageviewCache->clear();
	}
}