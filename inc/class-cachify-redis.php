<?php
/**
 * Class for Redis based caching.
 *
 * @package Cachify
 */

/* Quit */
defined( 'ABSPATH' ) || exit;

/**
 * Cachify_REDIS
 */
final class Cachify_REDIS {

	/**
	 * Availability check
	 *
	 * @since   2.0.7
	 * @change  2.0.7
	 *
	 * @return  boolean  true/false  TRUE when installed
	 */
	public static function is_available() {
		// $option = get_option( 'permalink_structure' );
		// return ! empty( $option );
		return true;
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
		return 'Redis';
	}

	/**
	 * Store item in cache
	 *
	 * @since   2.0
	 * @change  2.3.0
	 *
	 * @param   string  $hash        Hash  of the entry [ignored].
	 * @param   string  $data        Content of the entry.
	 * @param   integer $lifetime    Lifetime of the entry [ignored].
	 * @param   bool    $sig_detail  Show details in signature.
	 */
	public static function store_item( $hash, $data, $lifetime, $sig_detail ) {

	}

	/**
	 * Read item from cache
	 *
	 * @since   2.0
	 * @change  2.0
	 *
	 * @return  boolean  True if cache is present.
	 */
	public static function get_item() {
		return false;
	}

	/**
	 * Delete item from cache
	 *
	 * @since   2.0
	 * @change  2.0
	 *
	 * @param   string $hash  Hash of the entry [ignored].
	 * @param   string $url   URL of the entry.
	 */
	public static function delete_item( $hash, $url ) {

	}

	/**
	 * Clear the cache
	 *
	 * @since   2.0
	 * @change  2.0
	 */
	public static function clear_cache() {

	}

	/**
	 * Print the cache
	 *
	 * @since   2.0
	 * @change  2.3
	 */
	public static function print_cache() {

	}

	/**
	 * Get the cache size
	 *
	 * @since   2.0
	 * @change  2.0
	 *
	 * @return  integer  Directory size
	 */
	public static function get_stats() {
		return 0;
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
			( $detail ? 'Redis Cache' : __( 'Generated', 'cachify' ) ),
			date_i18n(
				'd.m.Y H:i:s',
				current_time( 'timestamp' )
			)
		);
	}

}
