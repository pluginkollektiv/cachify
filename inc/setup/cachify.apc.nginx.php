<?php
/* Quit */
defined('ABSPATH') OR exit;

$beginning = 'location ~ .php {
    include fastcgi_params;
    fastcgi_pass 127.0.0.1:9000;
    fastcgi_param PHP_VALUE auto_prepend_file=';

$ending = '/cachify/apc/proxy.php;

    location ~ /wp-admin/ {
        include fastcgi_params;
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_param PHP_VALUE auto_prepend_file=;
    }
}';

?>

	<table class="form-table">
		<tr>
			<th>
				<?php esc_html_e( 'php.ini APC setup', 'cachify' ); ?>
			</th>
			<td>
				<label for="cachify_setup">
					<?php esc_html_e( 'Please add the following lines to your php.ini file', 'cachify' ); ?>
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
				<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=LG5VC9KXMAYXJ" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Donate', 'cachify' ); ?></a>
				&bull; <a href="<?php esc_html_e( 'https://wordpress.org/plugins/cachify/faq/', 'cachify' ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'FAQ', 'cachify' ); ?></a>
				&bull; <a href="https://github.com/pluginkollektiv/cachify/wiki" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Manual', 'cachify' ); ?></a>
				&bull; <a href="https://wordpress.org/support/plugin/cachify" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Support', 'cachify' ); ?></a>
			</td>
		</tr>
	</table>
