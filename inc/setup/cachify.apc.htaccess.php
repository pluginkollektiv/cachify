<?php
/**
 * Setup for APC on Apache server.
 *
 * @package Cachify
 */

/* Quit */
defined( 'ABSPATH' ) || exit;

$beginning = '&lt;Files index.php&gt;
  php_value auto_prepend_file ';

$ending = '/cachify/apc/proxy.php
&lt;/Files&gt;';

?>

	<table class="form-table">
		<tr>
			<th>
			<?php esc_html_e( '.htaccess APC setup', 'cachify' ); ?>
			</th>
			<td>
				<label for="cachify_setup">
					<?php esc_html_e( 'Please add the following lines to your .htaccess file', 'cachify' ); ?>
				</label>
			</td>
		</tr>
	</table>

	<textarea rows="16" class="large-text code" name="code" id="cachify-code" readonly>
		<?php
		printf(
			'%s%s%s',
			esc_html( $beginning ),
			esc_html( WP_PLUGIN_DIR ),
			esc_html( $ending )
		);
		?>
	</textarea>
