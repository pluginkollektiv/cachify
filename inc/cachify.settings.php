<?php
/**
 * Settings page.
 *
 * @package Cachify
 */

/* Quit */
defined( 'ABSPATH' ) || exit;
?>

<form method="post" action="options.php">
	<?php settings_fields( 'cachify' ); ?>
	<table class="form-table">
		<tr>
			<th scope="row">
				<label for="cachify_cache_method"><?php esc_html_e( 'Cache method', 'cachify' ); ?></label>
			</th>
			<td>
				<select name="cachify[use_apc]" id="cachify_cache_method">
					<?php foreach ( self::_method_select() as $k => $v ) { ?>
						<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $options['use_apc'], $k ); ?>><?php echo esc_html( $v ); ?></option>
					<?php } ?>
				</select>
			</td>
		</tr>

		<tr>
			<th scope="row">
				<label for="cachify_cache_expires"><?php esc_html_e( 'Cache expiration', 'cachify' ); ?></label>
			</th>
			<td>
				<input type="number" min="0" step="1" name="cachify[cache_expires]" id="cachify_cache_expires" value="<?php echo esc_attr( $options['cache_expires'] ); ?>" class="small-text" />
				<?php esc_html_e( 'Hours', 'cachify' ); ?>
				<?php if ( self::METHOD_HDD === $options['use_apc'] ) : ?>
					<p class="description"><?php esc_html_e( 'HDD cache will only expire correctly when triggered by a system cron.', 'cachify' ); ?></p>
				<?php endif; ?>

				<p class="description">
					<?php
					printf(
						/* translators: Placeholder is the trash icon itself as dashicon */
						esc_html__( 'Flush the cache by clicking the button below or the %1$s icon in the admin bar.', 'cachify' ),
						'<span class="dashicons dashicons-trash" aria-hidden="true"></span><span class="screen-reader-text">"' . esc_html__( 'Flush the Cachify cache', 'cachify' ) . '"</span>'
					);
					?>
				</p>

				<?php
					$flush_cache_url = wp_nonce_url( add_query_arg( '_cachify', 'flush' ), '_cachify__flush_nonce' );
				?>

				<p>
					<a class="button button-secondary" href="<?php echo esc_url( $flush_cache_url ); ?>">
						<?php esc_html_e( 'Flush cache now', 'cachify' ); ?>
					</a>
				</p>
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

					<label for="cachify_reset_on_post">
						<input type="checkbox" name="cachify[reset_on_post]" id="cachify_reset_on_post" value="1" <?php checked( '1', $options['reset_on_post'] ); ?> />
						<?php esc_html_e( 'Flush the cache at modified posts', 'cachify' ); ?>
					</label>
					<p class="description">
						<?php esc_html_e( 'If selected, the site cache will be flushed, otherwise only the modified post is removed from the cache.', 'cachify' ); ?>
					</p>

					<br>

					<label for="cachify_reset_on_comment">
						<input type="checkbox" name="cachify[reset_on_comment]" id="cachify_reset_on_comment" value="1" <?php checked( '1', $options['reset_on_comment'] ); ?> />
						<?php esc_html_e( 'Flush the cache at new comments', 'cachify' ); ?>
					</label>
					<p class="description">
						<?php esc_html_e( 'If selected, the site cache will be flushed, otherwise only the corresponding post is removed from the cache.', 'cachify' ); ?>
					</p>
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
						<input type="text" name="cachify[without_ids]" id="cachify_without_ids" placeholder="<?php esc_attr_e( 'e.g. 1, 2, 3', 'cachify' ); ?>" value="<?php echo esc_attr( $options['without_ids'] ); ?>" />
						<?php esc_html_e( 'Post/Page IDs', 'cachify' ); ?>
					</label>

					<br />

					<label for="cachify_without_agents">
						<input type="text" name="cachify[without_agents]" id="cachify_without_agents" placeholder="<?php esc_attr_e( 'e.g. MSIE 6, Opera', 'cachify' ); ?>" value="<?php echo esc_attr( $options['without_agents'] ); ?>" />
						<?php esc_html_e( 'Browser User Agents', 'cachify' ); ?>
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
					<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $options['compress_html'], $k ); ?>>
						<?php echo esc_html( $v ); ?>
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

		<tr>
			<th scope="row">
				<?php esc_html_e( 'Robots.txt', 'cachify' ); ?>
			</th>
			<td>
				<input type="checkbox" name="cachify[change_robots_txt]" id="cachify_change_robots_txt" value="1" <?php checked( '1', $options['change_robots_txt'] ); ?> />
				<label for="cachify_change_robots_txt"><?php esc_html_e( 'Disallow access to cache folder via robots.txt', 'cachify' ); ?></label>
			</td>
		</tr>
	</table>

	<?php submit_button(); ?>
</form>
