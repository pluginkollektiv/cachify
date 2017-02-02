<?php
/* Quit */
defined('ABSPATH') OR exit;

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

	<div style="background:#fff;border:1px solid #ccc;padding:10px 20px">
		<pre><?php echo sprintf ( '%s%s%s',
				$beginning,
				WP_PLUGIN_DIR,
				$ending ); ?></pre>
	</div>

	<table class="form-table">
		<tr>
			<td>
				<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=8CH5FPR88QYML" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Donate', 'cachify' ); ?></a>
				&bull; <a href="<?php echo esc_url( __( 'https://wordpress.org/plugins/cachify/faq/', 'cachify' ), 'https' ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'FAQ', 'cachify' ); ?></a>
				&bull; <a href="https://github.com/pluginkollektiv/cachify/wiki" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Manual', 'cachify' ); ?></a>
				&bull; <a href="https://wordpress.org/support/plugin/cachify" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Support', 'cachify' ); ?></a>
			</td>
		</tr>
	</table>
