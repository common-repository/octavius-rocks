<?php

use OctaviusRocks\Plugin;
use OctaviusRocks\Server\ServerConfigurationHandler;
use OctaviusRocks\ServerConfigurationStore;
use OctaviusRocks\Settings;

/**
 * template file for Octavius License Page
 *
 * $submit_button_text        Text for submit button
 * $submit_button            Submit button identifier
 *
 * @var Settings $this
 */
$plugin                    = Plugin::instance();
$submit_path               = $plugin->settings->get_path_with_params( array( "tab" => "license" ) );
$config                    = ServerConfigurationStore::instance();
$was_last_fetch_successful = ServerConfigurationHandler::was_last_fetch_successful();
$clientConfig              = $config->get();
?>

<div class="wrap">
	<form method="post" action="<?php echo $submit_path ?>">
        <?php $this->nonce_field(); ?>

		<table class="form-table">
			<tr>
				<th scope="row">
					<label for="<?php echo Plugin::OPTION_API_KEY; ?>">
						<?php _e( "API Key", Plugin::DOMAIN ); ?>
					</label>
				</th>
				<td>
					<?php
					echo '<input class="regular-text" id="' . Plugin::OPTION_API_KEY . '" name="' . Plugin::OPTION_API_KEY . '" type="text"';
					if ( $config->is_api_key_defined_as_constant() ) {
						echo ' readonly="readonly" ';
						echo ' value="' . esc_attr($config->get_api_key()) . '" />';
					} else {
						echo ' value="' . esc_attr($config->get_api_key()) . '" />';
					}
					echo ( !empty($config->get_api_key()) && $was_last_fetch_successful ) ?
						" ✅" :
						" 🚨<br>" . __( "We could not fetch fresh server configuration settings. Please check your api key.", Plugin::DOMAIN );
					if ( $config->is_api_key_defined_as_constant() ) {
						echo '<br><span class="description" alt="Its defined in your code!">';
						_e( 'Your API key is a defined as a constant in your code. Probably in wp-config.php.', Plugin::DOMAIN );
						echo '</span>';
					}
					?>
				</td>
			</tr>
			<?php
			if ( $config->has_valid_configuration() ) {
				?>
				<tr>
					<th scope="row"><label
								for="<?php echo Plugin::OPTION_SERVER; ?>">
							<?php _e( "Server", Plugin::DOMAIN ); ?>
						</label>
					</th>
					<td>
						<?php
						echo "<p>{$config->get_server()}</p>";
						?>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="<?php echo Plugin::OPTION_SERVER_PATH; ?>">
							<?php _e( "Version", Plugin::DOMAIN ); ?>
						</label>
					</th>
					<td>
						<?php
						$readable = ( empty( $clientConfig->getVersionName() ) ) ? $config->get_server_path() : $clientConfig->getVersionName();
						echo "<p>{$readable}<p/>";
						?>
					</td>
				</tr>
				<?php
			} else {
				?>
				<tr>
					<th scope="row"></th>
					<td>
						<?php
						sprintf(
							__( "You need a valid API key. Goto %sOctavius.Rocks%s to get one." ),
							"<a href=\"https://octavius.rocks/\" target=\"_blank\">",
							"</a>"
						)
						?>
					</td>
				</tr>
				<?php
			}

			// --------------------
			// save connection button
			// --------------------
			?>
			<tr>
				<th scope="row"></th>
				<td>
					<?php
					submit_button(
						__( "Save and connect to Service", Plugin::DOMAIN ),
						"primary",
						"save_api_key"
					);
					?>
				</td>
			</tr>

		</table>

	</form>

	<?php
	$info = array();

	if ( ! defined( 'OCTAVIUS_ROCKS_API_KEY' ) ) {
		$apikey = ( empty( $config->get_api_key() ) ) ? "YOUR-API-KEY" : $config->get_api_key();
		$info[] = "define('OCTAVIUS_ROCKS_API_KEY', '$apikey');";
	}

	if ( count( $info ) > 0 ) {
		?>
		<hr>
		<p class="description">
			<?php _e("Tip: Use constants in wp-config.php for better performance", Plugin::DOMAIN); ?>
		</p>
		<code><?php
			echo implode( "<br>", $info );
			?></code>
		<?php
	}
	?>

</div>
