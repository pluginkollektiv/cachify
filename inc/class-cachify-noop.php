<?php
/**
 * Class for NO-OP caching.
 *
 * @package Cachify
 */

/* Quit */
defined( 'ABSPATH' ) || exit;

/**
 * Cachify_NOOP
 *
 * No-Op backend which does simply nothing.
 *
 * @since 2.4.0
 */
final class Cachify_NOOP implements Cachify_Backend {

	/**
	 * Name of the unavailable caching method, i.e. the reason why we chose no-op.
	 *
	 * @var string
	 */
	public $unavailable_method;

	/**
	 * Constructor with name of the unavailable method.
	 *
	 * @param string $unavailable_method Name of the unavailable method.
	 */
	public function __construct( string $unavailable_method = '' ) {
		$this->unavailable_method = $unavailable_method;
	}

	/**
	 * Availability check
	 *
	 * @return bool TRUE when installed
	 */
	public static function is_available(): bool {
		return true;
	}

	/**
	 * Caching method as string
	 *
	 * @return string Caching method
	 */
	public static function stringify_method(): string {
		return 'NOOP';
	}

	/**
	 * Store item in cache
	 *
	 * @param string $hash     Hash of the entry.
	 * @param string $data     Content of the entry.
	 * @param int    $lifetime Lifetime of the entry.
	 * @param bool   $sig_detail Show details in signature.
	 */
	public static function store_item( string $hash, string $data, int $lifetime, bool $sig_detail ): void {
		// NOOP.
	}

	/**
	 * Read item from cache
	 *
	 * @param string $hash Hash of the entry.
	 *
	 * @return false No content
	 */
	public static function get_item( string $hash ) {
		return false;
	}

	/**
	 * Delete item from cache
	 *
	 * @param string $hash Hash of the entry.
	 * @param string $url  URL of the entry [optional].
	 */
	public static function delete_item( string $hash, string $url = '' ): void {
		// NOOP.
	}

	/**
	 * Clear the cache
	 */
	public static function clear_cache(): void {
		// NOOP.
	}

	/**
	 * Print the cache
	 *
	 * @param bool  $sig_detail Show details in signature.
	 * @param array $cache      Array of cache values.
	 */
	public static function print_cache( bool $sig_detail, $cache ): void {
		// NOOP.
	}

	/**
	 * Get the cache size
	 *
	 * @return int Column size
	 */
	public static function get_stats(): int {
		return 0;
	}
}
