<?php
/**
 * Setup for HDD on Apache server.
 *
 * @package Cachify
 */

/* Quit */
defined( 'ABSPATH' ) || exit;

$beginning = '# BEGIN CACHIFY
&lt;IfModule mod_rewrite.c&gt;
  # ENGINE ON
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

  # gzip
  RewriteRule .* - [E=CACHIFY_SUFFIX:]
  &lt;IfModule mod_mime.c&gt;
    RewriteCond %{HTTP:Accept-Encoding} gzip
    RewriteRule .* - [E=CACHIFY_SUFFIX:.gz]
    AddType text/html .gz
    AddEncoding gzip .gz
  &lt;/IfModule&gt;

  # Main Rules
  RewriteCond %{REQUEST_METHOD} !=POST
  RewriteCond %{QUERY_STRING} =""
  RewriteCond %{REQUEST_URI} !^/(wp-admin|wp-content/cache)/.*
  RewriteCond %{HTTP_COOKIE} !(wp-postpass|wordpress_logged_in|comment_author)_
  RewriteCond ';

$middle = '/cache/cachify/%{ENV:CACHIFY_HOST}%{ENV:CACHIFY_DIR}index.html -f
  RewriteRule ^(.*) ';

$ending = '/cache/cachify/%{ENV:CACHIFY_HOST}%{ENV:CACHIFY_DIR}index.html%{ENV:CACHIFY_SUFFIX} [L]
&lt;/IfModule&gt;
# END CACHIFY';
?>

	<table class="form-table">
		<tr>
			<th>
				<?php esc_html_e( '.htaccess HDD setup', 'cachify' ); ?>
			</th>
			<td>
				<?php esc_html_e( 'Please add the following lines to your .htaccess file', 'cachify' ); ?>
			</td>
		</tr>

		<tr>
			<th>
				<?php esc_html_e( 'Notes', 'cachify' ); ?>
			</th>
			<td>
				<ul style="list-style-type:circle">
					<li>
						<?php esc_html_e( 'Within .htaccess, the extension has a higher priority and must be placed above the WordPress Rewrite rules (marked mostly by # BEGIN WordPress … # END WordPress).', 'cachify' ); ?>
					</li>
					<li>
						<?php esc_html_e( 'Changes to the .htaccess file can not be made if PHP is run as fcgi.', 'cachify' ); ?>
					</li>
					<li>
						<?php esc_html_e( 'If there are partial errors in the redirects within the blog, the shutdown of the Apache Content Cache can help:', 'cachify' ); ?><br />
						<pre>&lt;IfModule mod_cache.c&gt;
  CacheDisable /
&lt;/IfModule&gt;</pre>
					</li>
				</ul>
			</td>
		</tr>
	</table>

	<div style="background:#fff;border:1px solid #ccc;padding:10px 20px">
		<pre style="white-space: pre-wrap">
			<?php
			echo sprintf(
				'%s%s%s%s%s',
				esc_html( $beginning ),
				esc_html( WP_CONTENT_DIR ),
				esc_html( $middle ),
				esc_html( wp_make_link_relative( content_url() ) ),
				esc_html( $ending )
			);
			?>
		</pre>
	</div>
