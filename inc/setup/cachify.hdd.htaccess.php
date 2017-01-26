	<table class="form-table">
		<tr>
			<th>
			<?php esc_html_e( '.htaccess HDD setup', 'cachify' ); ?>
			</th>
			<td>
				<label for="cachify_setup">
					please add the following lines to your .htaccess file
				</label>
			</td>
		</tr>
	</table>

	<div name="cachify[hdd_htaccess]" style="background:#fff;border:1px solid #ccc;padding:10px 20px"><pre>
# BEGIN CACHIFY
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
RewriteCond %{DOCUMENT_ROOT}/wp-content/cache/cachify/%{ENV:CACHIFY_HOST}%{ENV:CACHIFY_DIR}index.html -f
RewriteRule ^(.*) /wp-content/cache/cachify/%{ENV:CACHIFY_HOST}%{ENV:CACHIFY_DIR}index.html%{ENV:CACHIFY_SUFFIX} [L]
&lt;/IfModule&gt;
# END CACHIFY
</pre></div>
