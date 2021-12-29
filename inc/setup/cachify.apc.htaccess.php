<?php
/**
 * Setup for APC on Apache server.
 *
 * @package Cachify
 */

/* Quit */
defined( 'ABSPATH' ) || exit;

$beginning = '&lt;Files index.php&gt;
  php_value auto_prepend_file ';

$ending = '/cachify/apc/proxy.php
&lt;/Files&gt;';

// phpcs:disable Squiz.PHP.EmbeddedPhp
?>

<h2><?php esc_html_e( '.htaccess APC setup', 'cachify' ); ?></h2>
<p><?php esc_html_e( 'Please add the following lines to your .htaccess file', 'cachify' ); ?></p>

<textarea rows="5" class="large-text code cachify-code" name="code" readonly><?php
	printf(
		'%s%s%s',
		esc_html( $beginning ),
		esc_html( WP_PLUGIN_DIR ),
		esc_html( $ending )
	);
	?></textarea>
