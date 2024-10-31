<?php

use OctaviusRocks\Plugin;

?>

<div class="wrap">

	<h2><?php _ex( "Configuration", "Healthcheck headline", Plugin::DOMAIN ); ?></h2>

	<?php
	if ( $this->plugin->config->has_valid_configuration() ) {
		echo "<p>✅ " . __( "configuration settings seem to be ok.", Plugin::DOMAIN ) . "</p>";
	} else {
		if ( $this->plugin->config->get_api_key() == "" ) {
			echo "<p>🚨 " . __( "The api key is missing.", Plugin::DOMAIN ) . "</p>";
		}
		if ( $this->plugin->config->get_server() == "" ) {
			echo "<p>🚨 " . __( "The server domain is missing.", Plugin::DOMAIN ) . "</p>";
		}
		if ( $this->plugin->config->get_server_path() == "" ) {
			echo "<p>🚨 " . __( "The server path is missing.", Plugin::DOMAIN ) . "</p>";
		}
	}

	$lastRun           = get_option( Plugin::OPTION_FETCH_CONFIG_LAST_RUN, '' );
	$lastSuccessfulRun = get_option( Plugin::OPTION_FETCH_CONFIG_LAST_RUN_SUCCESS, '' );
	$raw               = get_option( Plugin::OPTION_FETCH_CONFIG_RESULT, '' );

	if ( $lastSuccessfulRun != '' ) {
		echo "<p>✅ ";
		printf(
			__( "Last successful fetch of client configuration: ⏱ %s", Plugin::DOMAIN ),
			$lastSuccessfulRun
		);
		echo "</p>";
	}

	if ( $lastRun != $lastSuccessfulRun ) {
		echo "<p>🚨 ";
		_e("Something went wrong on fetching client configuration.", Plugin::DOMAIN);
		echo "</p>";
		if ( $lastRun != '' ) {
			echo "<p>⏱ ";
			printf(
				__("Last time tried to fetch client configuration: %s", Plugin::DOMAIN),
				$lastRun
			);
			echo "</p>";
		}
	}

	if ( $raw != '' ) {
		$raw = json_encode( $raw, JSON_PRETTY_PRINT );
		echo "<p>";
		printf(
			__("Last valid response:%s", Plugin::DOMAIN),
			"<br><pre>$raw</pre>"
		);
		echo "</p>";
	}


	?>

	<h2><?php _e("Crons", Plugin::DOMAIN); ?></h2>
	<?php
	if ( $this->plugin->schedule->isClientConfigScheduled() ) {
		echo "<p>✅ ";
		_e("Configuration is scheduled to be updated regularly.", Plugin::DOMAIN);
		echo "</p>";
	} else {
		echo "<p>🚨 ";
		_e("No schedule found for configuration.", Plugin::DOMAIN);
		echo "</p>";
	}
	$isScheduledPageviewsCache = $this->plugin->schedule->isScheduled(Plugin::SCHEDULE_FETCH_PAGEVIEWS);
	$isDisabledPageviewsCache = $this->plugin->schedule->isFetchPageviewsDisabled();
	if($isScheduledPageviewsCache && $isDisabledPageviewsCache){
		echo "<p>";
		_e("Pageviews cache cron is scheduled but disabled.", Plugin::DOMAIN);
		echo "</p>";
	} else if(!$isDisabledPageviewsCache && !$isScheduledPageviewsCache){
		echo "<p>🚨 </p>";
		_e("Pageviews cache cron is not scheduled.", Plugin::DOMAIN);
		echo "</p>";
	} else if(!$isScheduledPageviewsCache && $isDisabledPageviewsCache){
		echo "<p>ℹ️ ";
		_e( "Pageviews cache schedule is disabled. Maybe you use wp octavius-rocks import in a separate cronjob?", Plugin::DOMAIN );
		echo "</p>";
	} else {
		echo "<p>✅ ";
		_e( "Pageviews cache is scheduled to be updated regularly.", Plugin::DOMAIN );
		echo "</p>";
	}
	$lastPageviewsCacheImport = $this->plugin->pageviewCache->getLastImport();
	if(empty($lastPageviewsCacheImport)){
		echo "<p>⚠️";
		_e('Pageviews cache was not imported yet.', Plugin::DOMAIN);
		echo "</p>";
	} else {
		echo "<p>✅ ";
		printf(
			__( "Last successful pageviews cache import: ⏱ %s", Plugin::DOMAIN ),
			$lastPageviewsCacheImport
		);
		echo "</p>";
	}
	?>

	<h2><?php _e("Api Connection", Plugin::DOMAIN); ?></h2>
	<p>
		<?php
		$connection = \OctaviusRocks\ServerConfigurationStore::instance()
		                                                     ->connect();
		if ( $connection != NULL && $connection->checkConnection() ) {
			echo "✅ ";
			_e("Server is reachable.", Plugin::DOMAIN);
		} else {
			echo "🚨 ";
			_e("Server is unreachable.", Plugin::DOMAIN);
		}
		?>
	</p>

	<div id="octavius-health-api-connection"></div>

</div>
