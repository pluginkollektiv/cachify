<?php
/**
 * Class for database based caching.
 *
 * @package Cachify
 */

/* Quit */
defined( 'ABSPATH' ) || exit;

/**
 * Cachify_DB
 */
final class Cachify_DB implements Cachify_Backend {

	/**
	 * Availability check
	 *
	 * @return bool TRUE when installed
	 *
	 * @since 2.0.7
	 */
	public static function is_available(): bool {
		return true;
	}

	/**
	 * Caching method as string
	 *
	 * @return string Caching method
	 *
	 * @since 2.1.2
	 */
	public static function stringify_method(): string {
		return 'DB';
	}

	/**
	 * Store item in cache
	 *
	 * @param string $hash     Hash of the entry.
	 * @param string $data     Content of the entry.
	 * @param int    $lifetime Lifetime of the entry.
	 * @param bool   $sig_detail Show details in signature.
	 *
	 * @since 2.0
	 */
	public static function store_item( string $hash, string $data, int $lifetime, bool $sig_detail ): void {
		/* Do not store empty data. */
		if ( empty( $data ) ) {
			trigger_error( __METHOD__ . ': Empty input.', E_USER_WARNING );

			return;
		}

		/* Store */
		set_transient(
			$hash,
			array(
				'data' => $data,
				'meta' => array(
					'queries' => self::_page_queries(),
					'timer'   => self::_page_timer(),
					'memory'  => self::_page_memory(),
					'time'    => current_time( 'timestamp' ),
				),
			),
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
	public static function get_item( string $hash ) {
		return get_transient( $hash );
	}

	/**
	 * Delete item from cache
	 *
	 * @param string $hash Hash of the entry.
	 * @param string $url  URL of the entry [optional].
	 *
	 * @since 2.0
	 */
	public static function delete_item( string $hash, string $url = '' ): void {
		delete_transient( $hash );
	}

	/**
	 * Clear the cache
	 *
	 * @since 2.0
	 */
	public static function clear_cache(): void {
		/* Init */
		global $wpdb;

		/* Clear */
		$wpdb->query( 'DELETE FROM `' . $wpdb->options . "` WHERE `option_name` LIKE ('\_transient%.cachify')" );
	}

	/**
	 * Print the cache
	 *
	 * @param bool  $sig_detail Show details in signature.
	 * @param array $cache      Array of cache values.
	 *
	 * @since 2.0
	 * @since 2.3.0 added $sig_detail parameter
	 */
	public static function print_cache( bool $sig_detail, $cache ): void {
		/* No array? */
		if ( ! is_array( $cache ) ) {
			return;
		}

		/* Content */
		echo $cache['data']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		/* Signature - might contain runtime information, so it's generated at this point */
		if ( isset( $cache['meta'] ) ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo self::_cache_signature( $sig_detail, $cache['meta'] );
		}

		/* Quit */
		exit;
	}

	/**
	 * Get the cache size
	 *
	 * @return int Column size
	 *
	 * @since 2.0
	 */
	public static function get_stats(): int {
		/* Init */
		global $wpdb;

		/* Read */
		return intval(
			$wpdb->get_var(
				'SELECT SUM( CHAR_LENGTH(option_value) ) FROM `' . $wpdb->options . "` WHERE `option_name` LIKE ('\_transient%.cachify')"
			)
		);
	}

	/**
	 * Generate signature
	 *
	 * @param bool  $detail Show details in signature.
	 * @param mixed $meta   Content of metadata.
	 *
	 * @return string Signature string
	 *
	 * @since 2.0
	 * @since 2.3.0 added $detail parameter
	 */
	private static function _cache_signature( bool $detail, $meta ): string {
		/* No array? */
		if ( ! is_array( $meta ) ) {
			return '';
		}

		if ( $detail ) {
			return sprintf(
				"\n\n<!-- %s\n%s @ %s\n%s\n%s\n-->",
				'Cachify | https://cachify.pluginkollektiv.org',
				'DB Cache',
				date_i18n(
					'd.m.Y H:i:s',
					$meta['time']
				),
				sprintf(
					'Without Cachify: %d DB queries, %s seconds, %s',
					$meta['queries'],
					$meta['timer'],
					$meta['memory']
				),
				sprintf(
					'With Cachify: %d DB queries, %s seconds, %s',
					self::_page_queries(),
					self::_page_timer(),
					self::_page_memory()
				)
			);
		} else {
			return sprintf(
				"\n\n<!-- %s\n%s @ %s -->",
				'Cachify | https://cachify.pluginkollektiv.org',
				__( 'Generated', 'cachify' ),
				date_i18n(
					'd.m.Y H:i:s',
					$meta['time']
				)
			);
		}
	}

	/**
	 * Return query count
	 *
	 * @return int Number of queries
	 *
	 * @since 0.1
	 */
	private static function _page_queries(): int {
		return $GLOBALS['wpdb']->num_queries;
	}

	/**
	 * Return execution time
	 *
	 * @return string Execution time in seconds
	 *
	 * @since 0.1
	 */
	private static function _page_timer(): string {
		return timer_stop( 0, 2 );
	}

	/**
	 * Return memory consumption
	 *
	 * @return string Formatted memory size
	 *
	 * @since 0.7
	 */
	private static function _page_memory(): string {
		return ( function_exists( 'memory_get_usage' ) ? size_format( memory_get_usage(), 2 ) : 0 );
	}
}
