<?php
/**
 * Interface for caching storage classes.
 *
 * @package Cachify
 */

/* Quit */
defined( 'ABSPATH' ) || exit;

/**
 * Interface Cachify Backend
 */
interface Cachify_Backend {

	/**
	 * Availability check
	 *
	 * @return bool TRUE if available
	 */
	public static function is_available(): bool;

	/**
	 * Caching method as string
	 *
	 * @return string Caching method
	 */
	public static function stringify_method(): string;

	/**
	 * Store item in cache
	 *
	 * @param string $hash       Hash of the entry.
	 * @param string $data       Content of the entry.
	 * @param int    $lifetime   Lifetime of the entry.
	 * @param bool   $sig_detail Show details in signature.
	 */
	public static function store_item( string $hash, string $data, int $lifetime, bool $sig_detail ): void;

	/**
	 * Read item from cache
	 *
	 * @param string $hash Hash of the entry.
	 * @return mixed Content of the entry.
	 */
	public static function get_item( string $hash );

	/**
	 * Delete item from cache
	 *
	 * @param string $hash Hash of the entry.
	 * @param string $url  URL of the entry.
	 */
	public static function delete_item( string $hash, string $url ): void;

	/**
	 * Clear the cache
	 */
	public static function clear_cache(): void;

	/**
	 * Print the cache
	 *
	 * @param bool   $sig_detail  Show details in signature.
	 * @param string $cache       Cached content.
	 */
	public static function print_cache( bool $sig_detail, $cache ): void;
}
