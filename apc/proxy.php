<?php

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
		if ( 'on' === strtolower( $_SERVER['HTTPS'] ) ) {
			return true;
		}

		if ( '1' === $_SERVER['HTTPS'] ) {
			return true;
		}
	} elseif ( isset( $_SERVER['SERVER_PORT'] ) && ( '443' === $_SERVER['SERVER_PORT'] ) ) {
		return true;
	}

	return false;
}

if (
	empty( $_cachify_logged_in )
	&& ( strpos( filter_input( INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_STRING ), '/wp-admin/' ) === false )
	&& ( strpos( filter_input( INPUT_SERVER, 'HTTP_ACCEPT_ENCODING', FILTER_SANITIZE_STRING ), 'gzip' ) !== false )
	&& extension_loaded( 'apc' )
	&& ( $cache = apc_fetch( md5( ( cachify_is_ssl() ? 'https-' : '' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ) . '.cachify' ) )
) {
	ini_set( 'zlib.output_compression', 'Off' );

	header( 'Vary: Accept-Encoding' );
	header( 'X-Powered-By: Cachify' );
	header( 'Content-Encoding: gzip' );
	header( 'Content-Length: ' . strlen( $cache ) );
	header( 'Content-Type: text/html; charset=utf-8' );

	echo $cache;
	exit;
}
