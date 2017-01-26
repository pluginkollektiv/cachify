	<table class="form-table">
		<tr>
			<th>
			<?php esc_html_e( '.htaccess APC setup', 'cachify' ); ?>
			</th>
			<td>
				<label for="cachify_setup">
					Please add the following lines to your .htaccess file
				</label>
			</td>
		</tr>
	</table>

	<div name="cachify[apc_htaccess]" style="background:#fff;border:1px solid #ccc;padding:10px 20px"><pre>
<Files index.php>
    php_value auto_prepend_file /pfad/plugins/cachify/apc/proxy.php
</Files>
</pre></div>
