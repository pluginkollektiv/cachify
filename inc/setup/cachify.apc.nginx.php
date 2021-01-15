<?php
/**
 * Setup for APC on nginx server.
 *
 * @package Cachify
 */

/* Quit */
defined( 'ABSPATH' ) || exit;

$beginning = 'location ~ .php {
  include fastcgi_params;
  fastcgi_pass 127.0.0.1:9000;
  <strong>fastcgi_param PHP_VALUE auto_prepend_file=';

$ending = '/cachify/apc/proxy.php</strong>;

  location ~ /wp-admin/ {
    include fastcgi_params;
    fastcgi_pass 127.0.0.1:9000;
    <strong>fastcgi_param PHP_VALUE auto_prepend_file=</strong>;
  }
}';

?>

	<table class="form-table">
		<tr>
			<th>
				<?php esc_html_e( 'nginx APC setup', 'cachify' ); ?>
			</th>
			<td>
				<label for="cachify_setup">
					<?php esc_html_e( 'Please add the following lines to your nginx PHP configuration', 'cachify' ); ?>
				</label>
			</td>
		</tr>
	</table>

	<div style="background:#fff;border:1px solid #ccc;padding:10px 20px">
		<pre style="white-space: pre-wrap">
		<?php
		echo sprintf(
			'%s%s%s',
			$beginning,
			WP_PLUGIN_DIR,
			$ending
		);
		?>
		</pre>
	</div>

	<small>(<?php esc_html_e( 'You might need to adjust the non-highlighted lines to your needs.', 'cachify' ); ?>)</small>
