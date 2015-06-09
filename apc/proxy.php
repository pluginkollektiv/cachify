<?php

if ( ! empty($_COOKIE) ) {
	foreach ( $_COOKIE as $k => $v ) {
		if ( preg_match('/^(wp-postpass|wordpress_logged_in|comment_author)_/', $k) ) {
			$_cachify_logged_in = true; break;
		}
	}
}

if (
	empty($_cachify_logged_in)
	&& ( strpos( filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_STRING), '/wp-admin/' ) === false )
	&& ( strpos( filter_input(INPUT_SERVER, 'HTTP_ACCEPT_ENCODING', FILTER_SANITIZE_STRING), 'gzip' ) !== false )
	&& extension_loaded('apc')
	&& ( $cache = apc_fetch(md5($_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) . '.cachify') )
) {
	ini_set('zlib.output_compression', 'Off');

	header('Vary: Accept-Encoding');
	header('X-Powered-By: Cachify');
	header('Content-Encoding: gzip');
	header('Content-Length: ' .strlen($cache));
	header('Content-Type: text/html; charset=utf-8');

    echo $cache;
    exit;
}