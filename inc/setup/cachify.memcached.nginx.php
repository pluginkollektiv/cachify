<?php
/* Quit */
defined('ABSPATH') OR exit;
?>

	<table class="form-table">
		<tr>
			<th>
				<?php esc_html_e( 'nginxconf Memcached setup', 'cachify' ); ?>
			</th>
			<td>
				<label for="cachify_setup">
					<?php esc_html_e( 'Please add the following lines to your nginxconf', 'cachify' ); ?>
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
						<?php esc_html_e( 'For domains with FQDN, the variable ${http_host} must be used instead of ${host}.', 'cachify' ); ?>
					</li>
				</ul>
			</td>
		</tr>
	</table>

	<div name="cachify[memcached_nginx]" style="background:#fff;border:1px solid #ccc;padding:10px 20px"><pre>
## GZIP
gzip_static on;

## CHARSET
charset utf-8;

## INDEX LOCATION
location / {
    error_page 404 405 = @nocache;﻿

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
