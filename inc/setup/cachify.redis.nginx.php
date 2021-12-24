<?php
/**
 * Setup for Redis.
 *
 * @package Cachify
 */

/* Quit */
defined( 'ABSPATH' ) || exit;
?>

<h2><?php esc_html_e( 'Redis setup', 'cachify' ); ?></h2>
<p><?php esc_html_e( 'Please ensure the following variables are added to your environment', 'cachify' ); ?></p>

<textarea rows="16" class="large-text code cachify-code" name="code" readonly>
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
</textarea>

<small>(<?php esc_html_e( 'You might need to adjust the hostname and port to your needs.', 'cachify' ); ?>)</small>

