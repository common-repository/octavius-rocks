<?php

use OctaviusRocks\Plugin;
use OctaviusRocks\ServerConfigurationStore;
use OctaviusRocks\Settings;

/**
 * template file for Octavius Settings Page
 *
 * $submit_button_text		Text for submit button
 * $submit_button 			Submit button identifier
 *
 * @var Settings $this
 */
$plugin = Plugin::instance();
$submit_path = $plugin->settings->get_path_with_params(array("tab" => "advanced"));
$can_be_saved = false;
$store = ServerConfigurationStore::instance();
$clientConfig = $store->get();
$connection = $store->connect();

?>

<div class="wrap">
	<form method="post" action="<?php echo $submit_path ?>">

        <?php $this->nonce_field(); ?>
		<table class="form-table">

			<?php
			if($plugin->config->has_valid_configuration()){
				?>
				<tr>
					<th scope="row">
						<label for="<?php echo Plugin::OPTION_CLIENT_SECRET; ?>">
							<?php _e("Client Secret", Plugin::DOMAIN); ?>
						</label>
					</th>
					<td>
						<?php
						echo '<input class="regular-text" id="'.Plugin::OPTION_CLIENT_SECRET . '" name="'.Plugin::OPTION_CLIENT_SECRET . '"';
						if(defined('OCTAVIUS_ROCKS_CLIENT_SECRET')){
							echo ' value="'.OCTAVIUS_ROCKS_CLIENT_SECRET.'" ';
							echo ' readonly="readonly" ';
						} else {
							echo ' value="'.esc_attr($plugin->config->get_client_secret()) . '" ';
							$can_be_saved = true;
						}

						echo ' type="password"  />';
						echo ($connection != null && $connection->checkClientSecret($store->get_api_key()))? '✅':'🚨';
						?>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="<?php echo Plugin::OPTION_DISABLE_TRACK_CLICKS; ?>">
							<?php _e("Disable clicks", Plugin::DOMAIN); ?>
						</label>
					</th>
					<td>

						<?php
						if(defined('OCTAVIUS_ROCKS_TRACK_CLICKS')){
							if(OCTAVIUS_ROCKS_TRACK_CLICKS){
								_e("✓ yep, track it!", Plugin::DOMAIN);
							} else {
								_e("𝗑 nope, do not track it!", Plugin::DOMAIN);
							}
						} else {
							$can_be_saved = true;
							?>
							<p>
								<input type="hidden" name="<?php echo Plugin::OPTION_DISABLE_TRACK_CLICKS ?>_set" value="1">
								<input type="checkbox"
								       id="<?php echo Plugin::OPTION_DISABLE_TRACK_CLICKS; ?>"
								       name="<?php echo Plugin::OPTION_DISABLE_TRACK_CLICKS; ?>"
									<?= ($plugin->config->is_click_tracking_disabled())? "checked='checked'":""; ?>
									   value="disabled"
								/>
								<?php _e("Disable automatic link click tracking?", Plugin::DOMAIN); ?>
							</p>
							<?php
						}
						?>

					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="<?php echo Plugin::OPTION_DISABLE_TRACK_RENDERED; ?>">
							<?php _e("Disable rendered", Plugin::DOMAIN); ?>
						</label>
					</th>
					<td>

						<?php
						if(defined('OCTAVIUS_ROCKS_TRACK_RENDERED')){
							if(OCTAVIUS_ROCKS_TRACK_RENDERED){
								_e("✓ yep, track it!", Plugin::DOMAIN);
							} else {
								_e("𝗑 nope, do not track it!", Plugin::DOMAIN);
							}
						} else {
							$can_be_saved = true;
							?>
							<p>
								<input type="hidden" name="<?php echo Plugin::OPTION_DISABLE_TRACK_RENDERED ?>_set" value="1">
								<input type="checkbox"
								       id="<?php echo Plugin::OPTION_DISABLE_TRACK_RENDERED; ?>"
								       name="<?php echo Plugin::OPTION_DISABLE_TRACK_RENDERED; ?>"
									<?= ($plugin->config->is_rendered_tracking_disabled())? "checked='checked'":""; ?>
									   value="disabled"
								/>
								<?php _e("Disable automatic tracking of rendered elements?", Plugin::DOMAIN); ?>
							</p>
							<?php
						}
						?>

					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="<?php echo Plugin::OPTION_DISABLE_TRACK_PIXEL; ?>">
							<?php _e("Disable pixel", Plugin::DOMAIN); ?>
						</label>
					</th>
					<td>

						<?php
						if(defined('OCTAVIUS_ROCKS_TRACK_PIXEL')){
							if(OCTAVIUS_ROCKS_TRACK_PIXEL){
								_e("✓ yep, track it!", Plugin::DOMAIN);
							} else {
								_e("𝗑 nope, do not track it!", Plugin::DOMAIN);
							}
						} else {
							$can_be_saved = true;
							?>
							<p>
								<input type="hidden" name="<?php echo Plugin::OPTION_DISABLE_TRACK_PIXEL ?>_set" value="1">
								<input type="checkbox"
								       id="<?php echo Plugin::OPTION_DISABLE_TRACK_PIXEL; ?>"
								       name="<?php echo Plugin::OPTION_DISABLE_TRACK_PIXEL; ?>"
									<?= ($plugin->config->is_pixel_tracking_disabled())? "checked='checked'":""; ?>
									   value="disabled"
								/>
								<?php _e("Disable additional tracking pixel? It can provide information about javascript blocking users.", Plugin::DOMAIN); ?>
							</p>
							<?php
						}
						?>

					</td>
				</tr>
				<?php
			} else {
			?>
			<tr>
				<th scope="row"></th>
				<td>
					<?php _e("Waiting for valid api key...", Plugin::DOMAIN); ?>
				</td>
			</tr>
			<?php
			}
			?>
		</table>

		<?php
		if($can_be_saved) submit_button();
		?>

	</form>

	<?php
	$info = array();

	if(!defined('OCTAVIUS_ROCKS_CLIENT_SECRET'))
		$info[] = "define('OCTAVIUS_ROCKS_CLIENT_SECRET', '***');";

	if(!defined('OCTAVIUS_ROCKS_TRACK_CLICKS')){
		$value = ($plugin->config->is_click_tracking_disabled()) ? "false": "true";
		$info[] = "define('OCTAVIUS_ROCKS_TRACK_CLICKS', $value);";
	}

	if(!defined('OCTAVIUS_ROCKS_TRACK_RENDERED')){
		$value = ($plugin->config->is_rendered_tracking_disabled()) ? "false": "true";
		$info[] = "define('OCTAVIUS_ROCKS_TRACK_RENDERED', $value);";
	}

	if(!defined('OCTAVIUS_ROCKS_TRACK_PIXEL')){
		$value = ($plugin->config->is_pixel_tracking_disabled()) ? "false": "true";
		$info[] = "define('OCTAVIUS_ROCKS_TRACK_PIXEL', $value);";
	}


	if(count($info) > 0){
		?>
		<hr>
		<p class="description" >
			<?php _e("Tip: Use constants in wp-config.php for better performance", Plugin::DOMAIN); ?>
		</p>
		<code style="line-height: 1.6rem"><?php
		echo implode( "</code><br><code style=\"line-height: 1.6rem\">", $info);
		?></code>
	<?php
	}
	?>

</div>
