<?php
/* Quit */
defined( 'ABSPATH' ) || exit;

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
		<pre><?php echo sprintf( '%s%s%s',
			$beginning,
			WP_PLUGIN_DIR,
		$ending ); ?></pre>
	</div>
