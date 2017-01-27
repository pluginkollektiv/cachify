<?php
/* Quit */
defined('ABSPATH') OR exit;
?>

	<table class="form-table">
		<tr>
			<th>
				<?php esc_html_e( 'nginxconf HDD setup', 'cachify' ); ?>
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

	<div name="cachify[hdd_nginx]" style="background:#fff;border:1px solid #ccc;padding:10px 20px"><pre>
## GZIP
gzip_static on;

## CHARSET
charset utf-8;

## INDEX LOCATION
location / {
    if ( $query_string ) {
        return 405;
    }
    if ( $request_method = POST ) {
        return 405;
    }
    if ( $request_uri ~ /wp-admin/ ) {
        return 405;
    }
    if ( $http_cookie ~ (wp-postpass|wordpress_logged_in|comment_author)_ ) {
        return 405;
    }

    error_page 405 = @nocache;

    try_files /wp-content/cache/cachify/${host}${uri}index.html @nocache;
}

## NOCACHE LOCATION
location @nocache {
    try_files $uri $uri/ /index.php?$args;
}

## PROTECT CACHE
location ~ /wp-content/cache {
    internal;
}
</pre></div>
