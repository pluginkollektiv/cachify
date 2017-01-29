<?php
/* Quit */
defined('ABSPATH') OR exit;
?>

	<table class="form-table">
		<tr>
			<th scope="row">
				<?php esc_html_e( 'Cache method', 'cachify' ); ?>
			</th>
			<td>
				<label for="cachify_cache_method">
					<select name="cachify[use_apc]" id="cachify_cache_method">
						<?php foreach( self::_method_select() as $k => $v ) { ?>
							<option value="<?php echo esc_attr($k) ?>" <?php selected($options['use_apc'], $k); ?>><?php echo esc_html($v) ?></option>
						<?php } ?>
					</select>
				</label>
			</td>
		</tr>

		<tr>
			<th scope="row">
				<?php esc_html_e('Cache expiration', 'cachify') ?>
			</th>
			<td>
				<label for="cachify_cache_expires">
					<?php if ( $options [ 'use_apc' ] === self::METHOD_HDD): ?>&#8734;
						<?php else: ?><input type="number" min="0" step="1" name="cachify[cache_expires]" id="cachify_cache_expires" value="<?php echo esc_attr($options [ 'cache_expires' ] ) ?>" class="small-text" />
					<?php endif; ?>
					<?php esc_html_e( 'Hours', 'cachify' ); ?>
				</label>
			</td>
		</tr>

		<tr>
			<th scope="row">
				<?php esc_html_e( 'Cache generation', 'cachify' ); ?>
			</th>
			<td>
				<fieldset>
					<label for="cachify_only_guests">
						<input type="checkbox" name="cachify[only_guests]" id="cachify_only_guests" value="1" <?php checked('1', $options['only_guests']); ?> />
						<?php esc_html_e( 'No cache generation by logged in users', 'cachify' ); ?>
					</label>

					<br />

					<label for="cachify_reset_on_comment">
						<input type="checkbox" name="cachify[reset_on_comment]" id="cachify_reset_on_comment" value="1" <?php checked('1', $options['reset_on_comment']); ?> />
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
						<textarea name="cachify[without_ids]" id="cachify_without_ids" rows="1" placeholder="<?php esc_attr_e( 'e.g. 1,2,3', 'cachify' ); ?>"><?php echo esc_attr($options['without_ids']) ?></textarea>
						<?php esc_html_e( 'Post/Pages-IDs', 'cachify' ); ?>
					</label>

					<br />

					<label for="cachify_without_agents">
						<textarea name="cachify[without_agents]" id="cachify_without_agents" rows="1" placeholder="<?php esc_attr_e( 'e.g. MSIE 6, Opera', 'cachify' ); ?>"><?php echo esc_attr($options['without_agents']) ?></textarea>
						<?php esc_html_e( 'Browser User-Agents', 'cachify' ); ?>
					</label>
				</fieldset>
			</td>
		</tr>

		<tr>
			<th scope="row">
				<?php esc_html_e( 'Cache minify', 'cachify' ); ?>
			</th>
			<td>
				<label for="cachify_compress_html">
					<select name="cachify[compress_html]" id="cachify_compress_html">
						<?php foreach( self::_minify_select() as $k => $v ) { ?>
						<option value="<?php echo esc_attr($k) ?>" <?php selected($options['compress_html'], $k); ?>>
							<?php echo esc_html($v) ?>
						</option>
						<?php } ?>
					</select>
				</label>
			</td>
		</tr>

		<tr>
			<th scope="row">
				<?php submit_button() ?>
			</th>
			<td>
				<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=LG5VC9KXMAYXJ" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Donate', 'cachify' ); ?></a>
				&bull; <a href="<?php esc_html_e( 'https://wordpress.org/plugins/cachify/faq/', 'cachify' ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'FAQ', 'cachify' ); ?></a>
				&bull; <a href="https://github.com/pluginkollektiv/cachify/wiki" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Manual', 'cachify' ); ?></a>
				&bull; <a href="https://wordpress.org/support/plugin/cachify" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Support', 'cachify' ); ?></a>
			</td>
		</tr>
	</table>
