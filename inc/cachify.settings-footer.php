<?php
/**
 * Footer line of the settings page.
 *
 * @package Cachify
 */

/* Quit */
defined( 'ABSPATH' ) || exit;
?>

<p>
	<a href="<?php echo esc_url( __( 'https://cachify.pluginkollektiv.org/documentation/faq/', 'cachify' ), 'https' ); ?>" rel="noopener noreferrer"><?php esc_html_e( 'FAQ', 'cachify' ); ?></a>
	&bull; <a href="https://cachify.pluginkollektiv.org/documentation/" rel="noopener noreferrer"><?php esc_html_e( 'Manual', 'cachify' ); ?></a>
	&bull; <a href="https://wordpress.org/support/plugin/cachify/" rel="noopener noreferrer"><?php esc_html_e( 'Support', 'cachify' ); ?></a>
	&bull; <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=TD4AMD2D8EMZW" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Donate', 'cachify' ); ?></a>
</p>
