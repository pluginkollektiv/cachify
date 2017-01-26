<?php if ( $options [ 'use_apc' ] === self::METHOD_HDD) {
	if ($is_nginx) { include 'setup/cachify.hdd.nginx.php'; }
		else { include 'setup/cachify.hdd.htaccess.php'; }
} ?>

<?php if ( $options [ 'use_apc' ] === self::METHOD_APC) {
	if ($is_nginx) { include 'setup/cachify.apc.nginx.php'; }
		else { include 'setup/cachify.apc.htaccess.php'; }
} ?>

<?php if ( ( $options [ 'use_apc' ] === self::METHOD_MMC) && ($is_nginx) )
	{include 'setup/cachify.apc.nginx.php';
} ?>

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
