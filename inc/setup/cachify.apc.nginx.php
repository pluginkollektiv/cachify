<?php
/**
 * Setup for APC on nginx server.
 *
 * @package Cachify
 */

/* Quit */
defined( 'ABSPATH' ) || exit;

$nginx_conf = 'location ~ .php {
  include fastcgi_params;
  fastcgi_pass 127.0.0.1:9000;
  fastcgi_param PHP_VALUE auto_prepend_file=' . WP_PLUGIN_DIR . '/cachify/apc/proxy.php;

  location ~ /wp-admin/ {
    include fastcgi_params;
    fastcgi_pass 127.0.0.1:9000;
    fastcgi_param PHP_VALUE auto_prepend_file=;
  }
}';

// phpcs:disable Squiz.PHP.EmbeddedPhp
?>

<h2><?php esc_html_e( 'nginx APC setup', 'cachify' ); ?></h2>
<p><?php esc_html_e( 'Please add the following lines to your nginx PHP configuration', 'cachify' ); ?></p>

<textarea rows="13" class="large-text code cachify-code" name="code" readonly><?php
	echo esc_html( $nginx_conf );
?></textarea>

<small>(<?php esc_html_e( 'You might need to adjust the non-highlighted lines to your needs.', 'cachify' ); ?>)</small>
