	<table class="form-table">
		<tr>
			<th>
			<?php esc_html_e( 'php.ini APC setup', 'cachify' ); ?>
			</th>
			<td>
				<label for="cachify_setup">
					Please add the following lines to your php.ini file
				</label>
			</td>
		</tr>
	</table>

	<div name="cachify[apc_nginx]" style="background:#fff;border:1px solid #ccc;padding:10px 20px"><pre>
location ~ .php {
    include fastcgi_params;
    fastcgi_pass 127.0.0.1:9000;
    fastcgi_param PHP_VALUE auto_prepend_file=/pfad/plugins/cachify/apc/proxy.php;

    location ~ /wp-admin/ {
        include fastcgi_params;
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_param PHP_VALUE auto_prepend_file=;
    }
}
</pre></div>
