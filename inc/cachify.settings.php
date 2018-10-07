<?php
/* Quit */
defined( 'ABSPATH' ) || exit;
?>

<form method="post" action="options.php">
	<?php settings_fields( 'cachify' ) ?>
	<table class="form-table">
		<tr>
			<th scope="row">
				<label for="cachify_cache_method"><?php esc_html_e( 'Cache method', 'cachify' ); ?></label>
			</th>
			<td>
				<select name="cachify[use_apc]" id="cachify_cache_method">
					<?php foreach ( self::_method_select() as $k => $v ) { ?>
						<option value="<?php echo esc_attr( $k ) ?>" <?php selected( $options['use_apc'], $k ); ?>><?php echo esc_html( $v ) ?></option>
					<?php } ?>
				</select>
			</td>
		</tr>

		<tr>
			<th scope="row">
				<label for="cachify_cache_expires"><?php esc_html_e( 'Cache expiration', 'cachify' ) ?></label>
			</th>
			<td>
				<?php if ( self::METHOD_HDD === $options['use_apc'] ) : ?>
					<input type="number" min="0" step="1" name="cachify[cache_expires]" id="cachify_cache_expires" value="" disabled="disabled" class="small-text" /> Hours
					<p class="description"><?php esc_html_e( 'HDD cache will only expire as you update posts or flush it yourself.', 'cachify' ); ?></p>
				<?php else : ?>
					<input type="number" min="0" step="1" name="cachify[cache_expires]" id="cachify_cache_expires" value="<?php echo esc_attr( $options['cache_expires'] ) ?>" class="small-text" />
					<?php esc_html_e( 'Hours', 'cachify' ); ?>
				<?php endif; ?>

				<p>
					<label for="cachify_show_adminbar_button_caption">
						<input type="checkbox" name="cachify[show_adminbar_button_caption]" id="cachify_show_adminbar_button_caption" value="1" <?php checked( '1', $options['show_adminbar_button_caption'] ); ?> />
						<?php printf(
							/* translators: Placeholder is the trash icon itself as dashicon */
							esc_html__(
								'Show text caption next to the %1$s icon in the admin bar.',
								'Placeholder is the trash icon itself as dashicon',
								'cachify'
							),
							'<span class="dashicons dashicons-trash" aria-hidden="true"></span><span class="screen-reader-text">"' . esc_html__( 'Example of icon for button to flush the cachify cache', 'cachify' ) . '"</span>'
						); ?>
					</label>
				</p>

				<p class="description">
					<?php printf(
						/* translators: Placeholder is the trash icon itself as dashicon */
						esc_html_x(
							'Flush the cache by clicking the button below or the %1$s icon in the admin bar.',
							'Placeholder is the trash icon itself as dashicon',
							'cachify'
						),
						'<span class="dashicons dashicons-trash" aria-hidden="true"></span><span class="screen-reader-text">"' . esc_html__( 'Example of icon for button to flush the cachify cache', 'cachify' ) . '"</span>'
					); ?>
				</p>

				<p><a class="button button-flush" href="<?php echo wp_nonce_url( add_query_arg( '_cachify', 'flush' ), '_cachify__flush_nonce' ); ?>"><?php esc_html_e( 'Flush cache now', 'cachify' )  ?></a></p>

			</td>
		</tr>

		<tr>
			<th scope="row">
				<?php esc_html_e( 'Cache generation', 'cachify' ); ?>
			</th>
			<td>
				<fieldset>
					<label for="cachify_only_guests">
						<input type="checkbox" name="cachify[only_guests]" id="cachify_only_guests" value="1" <?php checked( '1', $options['only_guests'] ); ?> />
						<?php esc_html_e( 'No cache generation by logged in users', 'cachify' ); ?>
					</label>

					<br />

					<label for="cachify_reset_on_comment">
						<input type="checkbox" name="cachify[reset_on_comment]" id="cachify_reset_on_comment" value="1" <?php checked( '1', $options['reset_on_comment'] ); ?> />
						<?php esc_html_e( 'Flush the cache at new comments', 'cachify' ); ?>
					</label>
				</fieldset>
			</td>
		</tr>

		<tr>
			<th scope="row">
				<?php esc_html_e( 'Cache exceptions', 'cachify' ); ?>
			</th>
			<td>
				<fieldset>
					<label for="cachify_without_ids">
						<input type="text" name="cachify[without_ids]" id="cachify_without_ids" placeholder="<?php esc_attr_e( 'e.g. 1, 2, 3', 'cachify' ); ?>" value="<?php echo esc_attr( $options['without_ids'] ) ?>" />
						<?php esc_html_e( 'Post/Pages-IDs', 'cachify' ); ?>
					</label>

					<br />

					<label for="cachify_without_agents">
						<input type="text" name="cachify[without_agents]" id="cachify_without_agents" placeholder="<?php esc_attr_e( 'e.g. MSIE 6, Opera', 'cachify' ); ?>" value="<?php echo esc_attr( $options['without_agents'] ) ?>" />
						<?php esc_html_e( 'Browser User-Agents', 'cachify' ); ?>
					</label>
				</fieldset>
			</td>
		</tr>

		<tr>
			<th scope="row">
				<label for="cachify_compress_html"><?php esc_html_e( 'Cache minify', 'cachify' ); ?></label>
			</th>
			<td>
				<select name="cachify[compress_html]" id="cachify_compress_html">
					<?php foreach ( self::_minify_select() as $k => $v ) { ?>
					<option value="<?php echo esc_attr( $k ) ?>" <?php selected( $options['compress_html'], $k ); ?>>
						<?php echo esc_html( $v ) ?>
					</option>
					<?php } ?>
				</select>
			</td>
		</tr>

		<tr>
			<th scope="row">
				<?php esc_html_e( 'Cache signature', 'cachify' ); ?>
			</th>
			<td>
				<input type="checkbox" name="cachify[sig_detail]" id="cachify_sig_detail" value="1" <?php checked( '1', $options['sig_detail'] ); ?> />
				<label for="cachify_sig_detail"><?php esc_html_e( 'Add additional details to Cachify signature (HTML comment)', 'cachify' ); ?></label>
			</td>
		</tr>
	</table>

	<?php submit_button() ?>
</form>