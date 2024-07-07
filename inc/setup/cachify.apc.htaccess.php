<?php
/**
 * Setup for APC on Apache server.
 *
 * @package Cachify
 */

/* Quit */
defined( 'ABSPATH' ) || exit;

$htaccess = '<Files index.php>
  php_value auto_prepend_file ' . WP_PLUGIN_DIR . '/cachify/apc/proxy.php
</Files>';

// phpcs:disable Squiz.PHP.EmbeddedPhp
?>

<h2><?php esc_html_e( '.htaccess APC setup', 'cachify' ); ?></h2>
<p><?php esc_html_e( 'Please add the following lines to your .htaccess file', 'cachify' ); ?></p>

<textarea rows="5" class="large-text code cachify-code" name="code" readonly><?php
	echo esc_html( $htaccess );
?></textarea>
