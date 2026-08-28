<?php
/**
 * Setup for HDD on nginx server.
 *
 * @package Cachify
 */

/* Quit */
defined( 'ABSPATH' ) || exit;

$admin_path   = wp_parse_url( admin_url(), PHP_URL_PATH );
$content_path = wp_parse_url( content_url(), PHP_URL_PATH );
$admin_path   = untrailingslashit( '/' . ltrim( (string) $admin_path, '/' ) );
$content_path = untrailingslashit( '/' . ltrim( (string) $content_path, '/' ) );
$cache_path       = $content_path . '/cache/cachify';
$cache_url_path   = $content_path . '/cache';

$admin_path_regex = preg_quote( $admin_path, '/' );
$cache_path_regex = preg_quote( $cache_url_path, '/' );
$cookie_pattern   = Cachify::get_bypass_cookie_pattern();
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
  if ( $request_uri ~ <?php echo esc_html( $admin_path_regex ); ?>/ ) {
	return 405;
  }
  if ( $http_cookie ~ <?php echo esc_html( $cookie_pattern ); ?> ) {
	return 405;
  }

  error_page 405 = @nocache;

  try_files <?php echo esc_html( $cache_path ); ?>/https-${host}${uri}index.html <?php echo esc_html( $cache_path ); ?>/${host}${uri}index.html @nocache;
}

## NOCACHE LOCATION
location @nocache {
  try_files $uri $uri/ /index.php?$args;
}

## PROTECT CACHE
location ~ <?php echo esc_html( $cache_path_regex ); ?> {
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
