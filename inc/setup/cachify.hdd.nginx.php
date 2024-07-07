<?php
/**
 * Setup for HDD on nginx server.
 *
 * @package Cachify
 */

/* Quit */
defined( 'ABSPATH' ) || exit;
?>

<h2><?php esc_html_e( 'nginx HDD setup', 'cachify' ); ?></h2>
<p><?php esc_html_e( 'Please add the following lines to your nginx.conf', 'cachify' ); ?></p>

<textarea rows="16" class="large-text code cachify-code" name="code" readonly>
<?php if ( Cachify_HDD::is_gzip_enabled() ) : ?>
## GZIP
gzip_static on;

<?php endif; ?>
## CHARSET
charset utf-8;

## INDEX LOCATION
location / {
  if ( $query_string ) {
	return 405;
  }
  if ( $http_accept !~* "text/html" ) {
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

  try_files /wp-content/cache/cachify/https-${host}${uri}index.html /wp-content/cache/cachify/${host}${uri}index.html @nocache;
}

## NOCACHE LOCATION
location @nocache {
  try_files $uri $uri/ /index.php?$args;
}

## PROTECT CACHE
location ~ /wp-content/cache {
  internal;
}
</textarea>

<small>(<?php esc_html_e( 'You might need to adjust the location directives to your needs.', 'cachify' ); ?>)</small>

<h3><?php esc_html_e( 'Notes', 'cachify' ); ?></h3>
<ol>
	<li>
		<?php
		printf(
			/* translators: variable names*/
			esc_html__( 'For domains with FQDN, the variable %1$s must be used instead of %2$s.', 'cachify' ),
			'<code>${http_host}</code>',
			'<code>${host}</code>'
		);
		?>
	</li>
</ol>
