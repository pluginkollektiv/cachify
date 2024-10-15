<?php
/**
 * Setup for HDD on Apache server.
 *
 * @package Cachify
 */

/* Quit */
defined( 'ABSPATH' ) || exit;

$htaccess = '# BEGIN CACHIFY
<IfModule mod_rewrite.c>
  RewriteEngine on

  # set hostname directory
  RewriteCond %{HTTPS} on
  RewriteRule .* - [E=CACHIFY_HOST:https-%{HTTP_HOST}]
  RewriteCond %{HTTPS} off
  RewriteRule .* - [E=CACHIFY_HOST:%{HTTP_HOST}]

  # set subdirectory
  RewriteCond %{REQUEST_URI} /$
  RewriteRule .* - [E=CACHIFY_DIR:%{REQUEST_URI}]
  RewriteCond %{REQUEST_URI} ^$
  RewriteRule .* - [E=CACHIFY_DIR:/]
';

if ( Cachify_HDD::is_gzip_enabled() ) {
	$htaccess .= '
  # gzip
  RewriteRule .* - [E=CACHIFY_SUFFIX:]
  <IfModule mod_mime.c>
    RewriteCond %{HTTP:Accept-Encoding} gzip
    RewriteRule .* - [E=CACHIFY_SUFFIX:.gz]
    AddType text/html .gz
    AddEncoding gzip .gz
  </IfModule>
';
}

$htaccess .= '
  # Main Rules
  RewriteCond %{HTTP_ACCEPT} .*text/html.*
  RewriteCond %{REQUEST_METHOD} GET
  RewriteCond %{QUERY_STRING} ^$
  RewriteCond %{REQUEST_URI} !^/(wp-admin|wp-content/cache)/.*
  RewriteCond %{HTTP_COOKIE} !(wp-postpass|wordpress_logged_in|comment_author)_
  RewriteCond ' . WP_CONTENT_DIR . '/cache/cachify/%{ENV:CACHIFY_HOST}%{ENV:CACHIFY_DIR}index.html%{ENV:CACHIFY_SUFFIX} -f
  RewriteRule ^(.*) ' . wp_make_link_relative( content_url() ) . '/cache/cachify/%{ENV:CACHIFY_HOST}%{ENV:CACHIFY_DIR}index.html%{ENV:CACHIFY_SUFFIX} [L]
</IfModule>
# END CACHIFY';

// phpcs:disable Squiz.PHP.EmbeddedPhp
?>

<h2><?php esc_html_e( '.htaccess HDD setup', 'cachify' ); ?></h2>
<p><?php esc_html_e( 'Please add the following lines to your .htaccess file', 'cachify' ); ?></p>

<textarea rows="16" class="large-text code cachify-code" name="code" readonly><?php
	echo esc_html( $htaccess );
?></textarea>

<h3><?php esc_html_e( 'Notes', 'cachify' ); ?></h3>
<ol>
	<li>
		<?php esc_html_e( 'Within .htaccess, the extension has a higher priority and must be placed above the WordPress Rewrite rules (marked mostly by # BEGIN WordPress … # END WordPress).', 'cachify' ); ?>
	</li>
	<li>
		<?php esc_html_e( 'Changes to the .htaccess file can not be made if PHP is run as FastCGI.', 'cachify' ); ?>
	</li>
	<li>
		<?php esc_html_e( 'If there are partial errors in the redirects within the blog, the shutdown of the Apache Content Cache can help:', 'cachify' ); ?><br />
		<pre>&lt;IfModule mod_cache.c&gt;
  CacheDisable /
&lt;/IfModule&gt;</pre>
	</li>
	<li>
		<?php esc_html_e( 'In case of special character errors, you can add the following to the .htaccess file:', 'cachify' ); ?><br />
		<pre>AddDefaultCharset UTF-8</pre>
	</li>
</ol>
