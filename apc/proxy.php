<?php
/**
 * Proxy for APC based caching.
 *
 * @package Cachify
 */

if ( ! empty( $_COOKIE ) ) {
	foreach ( $_COOKIE as $k => $v ) {
		if ( preg_match( '/^(wp-postpass|wordpress_logged_in|comment_author)_/', $k ) ) {
			$_cachify_logged_in = true;
			break;
		}
	}
}

/**
 * Determines if SSL is used.
 *
 * @see is_ssl() (wp-includes/load.php).
 *
 * @return bool True if SSL, otherwise false.
 */
function cachify_is_ssl() {
	if ( isset( $_SERVER['HTTPS'] ) ) {
		$https = filter_input( INPUT_SERVER, 'HTTPS', FILTER_SANITIZE_STRING );
		if ( 'on' === strtolower( $https ) || '1' === $https ) {
			return true;
		}
	} elseif ( isset( $_SERVER['SERVER_PORT'] ) && ( '443' === $_SERVER['SERVER_PORT'] ) ) {
		return true;
	}

	return false;
}

if (
	empty( $_cachify_logged_in )
	&& extension_loaded( 'apc' )
	&& ( strpos( filter_input( INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_STRING ), '/wp-admin/' ) === false )
	&& ( strpos( filter_input( INPUT_SERVER, 'HTTP_ACCEPT_ENCODING', FILTER_SANITIZE_STRING ), 'gzip' ) !== false )
) {
	$cache = apc_fetch(
		md5(
			( cachify_is_ssl() ? 'https-' : '' ) .
			filter_input( INPUT_SERVER, 'HTTP_HOST', FILTER_SANITIZE_STRING ) .
			filter_input( INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_URL )
		) .
		'.cachify'
	);
	if ( $cache ) {
		ini_set( 'zlib.output_compression', 'Off' );

		header( 'Vary: Accept-Encoding' );
		header( 'X-Powered-By: Cachify' );
		header( 'Content-Encoding: gzip' );
		header( 'Content-Length: ' . strlen( $cache ) );
		header( 'Content-Type: text/html; charset=utf-8' );

		echo $cache; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		exit;
	}
}
