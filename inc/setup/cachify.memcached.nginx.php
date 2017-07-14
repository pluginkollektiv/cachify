<?php
/* Quit */
defined( 'ABSPATH' ) || exit;
?>

	<table class="form-table">
		<tr>
			<th>
				<?php esc_html_e( 'nginx Memcached setup', 'cachify' ); ?>
			</th>
			<td>
				<label for="cachify_setup">
					<?php esc_html_e( 'Please add the following lines to your nginx.conf', 'cachify' ); ?>
				</label>
			</td>
		</tr>

		<tr>
			<th>
				<?php esc_html_e( 'Notes', 'cachify' ); ?>
			</th>
			<td>
				<ul style="list-style-type:circle">
					<li>
						<?php echo sprintf(
							esc_html__( 'For domains with FQDN, the variable %s must be used instead of %s.', 'cachify' ),
							'<code>${http_host}</code>',
							'<code>${host}</code>'
						); ?>
					</li>
					<li>
						<?php echo sprintf(
							esc_html__( 'If you have errors please try to change %1$s to %2$s This forces IPv4 because some servers that allow IPv4 and IPv6 are configured to bind memcached to IPv4 only.', 'cachify' ),
							'<code>memcached_pass localhost:11211;</code>',
							'<code>memcached_pass 127.0.0.1:11211;</code>'
						); ?>
					</li>
				</ul>
			</td>
		</tr>
	</table>

	<div style="background:#fff;border:1px solid #ccc;padding:10px 20px"><pre style="white-space: pre-wrap">
## GZIP
gzip_static on;

## CHARSET
charset utf-8;

## INDEX LOCATION
location / {
  error_page 404 405 = @nocache;

  if ( $query_string ) {
    return 405;
  }
  if ( $request_method = POST ) {
    return 405;
  }
  if ( $request_uri ~ "/wp-" ) {
    return 405;
  }
  if ( $http_cookie ~ (wp-postpass|wordpress_logged_in|comment_author)_ ) {
    return 405;
  }

  default_type text/html;
  add_header X-Powered-By Cachify;
  set $memcached_key $host$uri;
  memcached_pass localhost:11211;
}

## NOCACHE LOCATION
location @nocache {
  try_files $uri $uri/ /index.php?$args;
}
</pre></div>

<small>(<?php esc_html_e( 'You might need to adjust the location directives to your needs.', 'cachify' ); ?>)</small>
