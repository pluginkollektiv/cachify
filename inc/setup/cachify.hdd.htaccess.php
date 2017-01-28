<?php
/* Quit */
defined('ABSPATH') OR exit;

$beginning = '# BEGIN CACHIFY
&lt;IfModule mod_cache.c&gt;
# Disables Apache Content Cache which could cause partial redirects errors
CacheDisable /
&lt;/IfModule&gt;

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
				</ul>
			</td>
		</tr>
	</table>

	<div style="background:#fff;border:1px solid #ccc;padding:10px 20px">
		<pre><?php echo sprintf ( '%s%s%s%s%s',
				$beginning,
				WP_CONTENT_DIR,
				$middle,
				content_url( $path ),
				$ending ); ?></pre>
	</div>

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
