<?php
/**
 * Class for APC based caching.
 *
 * @package Cachify
 */

/**
 * Cachify_APC
 */
final class Cachify_APC {

	/**
	 * Availability check
	 *
	 * @since   2.0.7
	 * @change  2.0.7
	 *
	 * @return  boolean  true/false  TRUE when installed
	 */
	public static function is_available() {
		return extension_loaded( 'apc' );
	}

	/**
	 * Caching method as string
	 *
	 * @since   2.1.2
	 * @change  2.1.2
	 *
	 * @return  string  Caching method
	 */
	public static function stringify_method() {
		return 'APC';
	}

	/**
	 * Store item in cache
	 *
	 * @since   2.0
	 * @change  2.3.0
	 *
	 * @param   string  $hash        Hash of the entry.
	 * @param   string  $data        Content of the entry.
	 * @param   integer $lifetime    Lifetime of the entry.
	 * @param   bool    $sig_detail  Show details in signature.
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
	 * @since   2.0
	 * @change  2.0
	 *
	 * @param   string $hash  Hash of the entry.
	 * @return  mixed         Content of the entry
	 */
	public static function get_item( $hash ) {
		return ( function_exists( 'apc_exists' ) ? apc_exists( $hash ) : apc_fetch( $hash ) );
	}

	/**
	 * Delete item from cache
	 *
	 * @since   2.0
	 * @change  2.0
	 *
	 * @param   string $hash  Hash of the entry.
	 * @param   string $url   URL of the entry [optional].
	 */
	public static function delete_item( $hash, $url = '' ) {
		apc_delete( $hash );
	}

	/**
	 * Clear the cache
	 *
	 * @since   2.0.0
	 * @change  2.0.7
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
	 * @since   2.0
	 * @change  2.0
	 */
	public static function print_cache() {
		return;
	}

	/**
	 * Get the cache size
	 *
	 * @since   2.0
	 * @change  2.0
	 *
	 * @return  mixed  Cache size
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
	 * @since   2.0
	 * @change  2.3.0
	 *
	 * @param   bool $detail  Show details in signature.
	 * @return  string        Signature string
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
