<?php

use OctaviusRocks\Plugin;

/**
 * template file for Octavius Settings Page
 *
 */
$plugin = Plugin::get_instance();
$submit_path = $plugin->settings->get_path_with_params(array("tab" => "general"));

?>

<div class="wrap">
	<form method="post" action="<?php echo $submit_path ?>">

		<table class="form-table">
			<tr>
				<th scope="row">
					<label for="<?php echo Plugin::OPTION_BREAKPOINTS_CSS; ?>">
						<?php _e("Breakpoints", Plugin::DOMAIN); ?>
					</label>
					<br>
					<p class="description">
						<?php _e("Use body:before presudo Element add breakpoint information to octavius", Plugin::DOMAIN); ?>
					</p>
				</th>
				<td><textarea
							id="<?php echo Plugin::OPTION_BREAKPOINTS_CSS; ?>"
							name="<?php echo Plugin::OPTION_BREAKPOINTS_CSS; ?>"
							class="regular-text"
							rows="10"
					></textarea></td>
				<td>
					<?php _e("Default:", Plugin::DOMAIN); ?><br>
					<code>
						body:before{<br>
						&emsp;&emsp;display:"none";<br>
						&emsp;&emsp;content: "mobile";<br>
						}<br>
						@media (min-width: 960px){<br>
						&emsp;&emsp;body:before {<br>
						&emsp;&emsp;content: "desktop";<br>
						}<br>
					</code>
				</td>
			</tr>
		</table>

		<?php submit_button(); ?>

	</form>
</div>
