<?php
/**
 * Cachify: Cachify DB backend
 *
 * This file contains the database caching backend.
 *
 * @package   Cachify
 */

/**
 * Cachify_DB
 */
final class Cachify_DB {

	/**
	 * Availability check
	 *
	 * @return  boolean  true/false  TRUE when installed
	 * @since   2.0.7
	 * @change  2.0.7
	 */
	public static function is_available() {
		return true;
	}

	/**
	 * Caching method as string
	 *
	 * @return  string  Caching method
	 * @since   2.1.2
	 * @change  2.1.2
	 */
	public static function stringify_method() {
		return 'DB';
	}

	/**
	 * Store item in cache
	 *
	 * @param string  $hash     Hash of the entry.
	 * @param string  $data     Content of the entry.
	 * @param integer $lifetime Lifetime of the entry.
	 *
	 * @since   2.0
	 * @change  2.0
	 */
	public static function store_item( $hash, $data, $lifetime ) {
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
	 * @return  mixed           Content of the entry
	 * @since   2.0
	 * @change  2.0
	 */
	public static function get_item( $hash ) {
		return get_transient( $hash );
	}

	/**
	 * Delete item from cache
	 *
	 * @param string $hash Hash of the entry.
	 * @param string $url  URL of the entry [optional].
	 *
	 * @since   2.0
	 * @change  2.0
	 */
	public static function delete_item( $hash, $url = '' ) {
		delete_transient( $hash );
	}

	/**
	 * Clear the cache
	 *
	 * @since   2.0
	 * @change  2.0
	 */
	public static function clear_cache() {
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
	 * @since   2.0
	 * @change  2.3.0
	 */
	public static function print_cache( $sig_detail, $cache ) {
		/* No array? */
		if ( ! is_array( $cache ) ) {
			return;
		}

		/* Content */
		echo $cache['data'];

		/* Signature - might contain runtime information, so it's generated at this point */
		if ( isset( $cache['meta'] ) ) {
			echo self::_cache_signature( $sig_detail, $cache['meta'] );
		}

		/* Quit */
		exit;
	}

	/**
	 * Get the cache size
	 *
	 * @return  integer  Column size
	 * @since   2.0
	 * @change  2.0
	 */
	public static function get_stats() {
		/* Init */
		global $wpdb;

		/* Read */

		return $wpdb->get_var(
			'SELECT SUM( CHAR_LENGTH(option_value) ) FROM `' . $wpdb->options . "` WHERE `option_name` LIKE ('\_transient%.cachify')"
		);
	}

	/**
	 * Generate signature
	 *
	 * @param bool  $detail Show details in signature.
	 * @param array $meta   Content of metadata.
	 *
	 * @return  string         Signature string
	 * @since   2.0
	 * @change  2.3.0
	 */
	private static function _cache_signature( $detail, $meta ) {
		/* No array? */
		if ( ! is_array( $meta ) ) {
			return;
		}

		if ( $detail ) {
			return sprintf(
				"\n\n<!-- %s\n%s @ %s\n%s\n%s\n-->",
				'Cachify | http://cachify.de',
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
				'Cachify | http://cachify.de',
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
	 * @return  integer  Number of queries
	 * @since   0.1
	 * @change  2.0
	 */
	private static function _page_queries() {
		return $GLOBALS['wpdb']->num_queries;
	}

	/**
	 * Return execution time
	 *
	 * @return  integer  Execution time in seconds
	 * @since   0.1
	 * @change  2.0
	 */
	private static function _page_timer() {
		return timer_stop( 0, 2 );
	}

	/**
	 * Return memory consumption
	 *
	 * @return  string  Formatted memory size
	 * @since   0.7
	 * @change  2.0
	 */
	private static function _page_memory() {
		return ( function_exists( 'memory_get_usage' ) ? size_format( memory_get_usage(), 2 ) : 0 );
	}
}
