<?php
/**
 * Class for APC based caching.
 *
 * @package Cachify
 */

/* Quit */
defined( 'ABSPATH' ) || exit;

/**
 * Cachify_APC
 */
final class Cachify_APC implements Cachify_Backend {

	/**
	 * Availability check
	 *
	 * @return bool TRUE when installed
	 *
	 * @since 2.0.7
	 */
	public static function is_available() {
		return extension_loaded( 'apc' );
	}

	/**
	 * Caching method as string
	 *
	 * @return string Caching method
	 *
	 * @since 2.1.2
	 */
	public static function stringify_method() {
		return 'APC';
	}

	/**
	 * Store item in cache
	 *
	 * @param string $hash       Hash of the entry.
	 * @param string $data       Content of the entry.
	 * @param int    $lifetime   Lifetime of the entry.
	 * @param bool   $sig_detail Show details in signature.
	 *
	 * @since 2.0
	 * @since 2.3.0 added $sigDetail parameter
	 */
	public static function store_item( $hash, $data, $lifetime, $sig_detail ) {
		/* Do not store empty data. */
		if ( empty( $data ) ) {
			trigger_error( __METHOD__ . ': Empty input.', E_USER_WARNING );
			return;
		}

		/* Store */
		apc_store(
			$hash,
			gzencode( $data . self::_cache_signature( $sig_detail ), 9 ),
			$lifetime
		);
	}

	/**
	 * Read item from cache
	 *
	 * @param string $hash Hash of the entry.
	 *
	 * @return mixed Content of the entry
	 *
	 * @since 2.0
	 */
	public static function get_item( $hash ) {
		return ( function_exists( 'apc_exists' ) ? apc_exists( $hash ) : apc_fetch( $hash ) );
	}

	/**
	 * Delete item from cache
	 *
	 * @param string $hash Hash of the entry.
	 * @param string $url  URL of the entry [optional].
	 *
	 * @since 2.0
	 */
	public static function delete_item( $hash, $url = '' ) {
		apc_delete( $hash );
	}

	/**
	 * Clear the cache
	 *
	 * @since 2.0
	 */
	public static function clear_cache() {
		if ( ! self::is_available() ) {
			return;
		}

		@apc_clear_cache( 'user' );
	}

	/**
	 * Print the cache
	 *
	 * @param bool   $sig_detail  Show details in signature.
	 * @param string $cache       Cached content.
	 *
	 * @since 2.0
	 */
	public static function print_cache( $sig_detail, $cache ) {
		// Not supported.
	}

	/**
	 * Get the cache size
	 *
	 * @return mixed Cache size
	 *
	 * @since 2.0
	 */
	public static function get_stats() {
		/* Info */
		$data = apc_cache_info( 'user' );

		/* Empty */
		if ( empty( $data['mem_size'] ) ) {
			return null;
		}

		return $data['mem_size'];
	}

	/**
	 * Generate signature
	 *
	 * @param bool $detail Show details in signature.
	 *
	 * @return string Signature string
	 *
	 * @since 2.0
	 * @since 2.3.0 added $detail parameter
	 */
	private static function _cache_signature( $detail ) {
		return sprintf(
			"\n\n<!-- %s\n%s @ %s -->",
			'Cachify | https://cachify.pluginkollektiv.org',
			( $detail ? 'APC Cache' : __( 'Generated', 'cachify' ) ),
			date_i18n(
				'd.m.Y H:i:s',
				current_time( 'timestamp' )
			)
		);
	}
}
