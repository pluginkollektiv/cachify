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
						<?php esc_html_e( 'For domains with FQDN, the variable ${http_host} must be used instead of ${host}.', 'cachify' ); ?>
					</li>
				</ul>
			</td>
		</tr>
	</table>

	<div style="background:#fff;border:1px solid #ccc;padding:10px 20px"><pre>
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
