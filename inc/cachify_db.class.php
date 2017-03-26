<?php

/* Quit */
defined( 'ABSPATH' ) || exit;

/**
* Cachify_DB
*/
final class Cachify_DB {

	/**
	 * Availability check
	 *
	 * @since   2.0.7
	 * @change  2.0.7
	 *
	 * @return  boolean  true/false  TRUE when installed
	 */
	public static function is_available() {
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
		return 'DB';
	}

	/**
	 * Store item in cache
	 *
	 * @since   2.0
	 * @change  2.0
	 *
	 * @param   string  $hash      Hash of the entry.
	 * @param   string  $data      Content of the entry.
	 * @param   integer $lifetime  Lifetime of the entry.
	 */
	public static function store_item( $hash, $data, $lifetime ) {
		/* Empty? */
		if ( empty( $hash ) || empty( $data ) ) {
			wp_die( 'DB add item: Empty input.' );
		}

		/* Store */
		set_transient(
			$hash,
			array(
				'data' => $data,
				'meta' => array(
					'queries' => self::_page_queries(),
					'timer'	  => self::_page_timer(),
					'memory'  => self::_page_memory(),
					'time'	  => current_time( 'timestamp' ),
				),
			),
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
	 * @return  mixed          Content of the entry
	 */
	public static function get_item( $hash ) {
		/* Leer? */
		if ( empty( $hash ) ) {
			wp_die( 'DB get item: Empty input.' );
		}

		return get_transient( $hash );
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
		/* Empty? */
		if ( empty( $hash ) ) {
			wp_die( 'DB delete item: Empty input.' );
		}

		/* Delete */
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
	 * @since   2.0
	 * @change  2.0.2
	 *
	 * @param   array $cache  Array of cache values.
	 */
	public static function print_cache( $cache ) {
		/* No array? */
		if ( ! is_array( $cache ) ) {
			return;
		}

		/* Content */
		echo $cache['data'];

		/* Signature */
		if ( isset( $cache['meta'] ) ) {
			echo self::_cache_signatur( $cache['meta'] );
		}

		/* Quit */
		exit;
	}

	/**
	 * Get the cache size
	 *
	 * @since   2.0
	 * @change  2.0
	 *
	 * @return  integer  Column size
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
	 * @since   2.0
	 * @change  2.0.5
	 *
	 * @param   array $meta  Content of metadata.
	 * @return  string       Signature string
	 */
	private static function _cache_signatur( $meta ) {
		/* No array? */
		if ( ! is_array( $meta ) ) {
			return;
		}

		return sprintf(
			"\n\n<!--\n%s\n%s\n%s\n%s\n-->",
			'Cachify | http://cachify.de',
			sprintf(
				'Ohne Plugin: %d DB-Anfragen, %s Sekunden, %s',
				$meta['queries'],
				$meta['timer'],
				$meta['memory']
			),
			sprintf(
				'Mit Plugin: %d DB-Anfragen, %s Sekunden, %s',
				self::_page_queries(),
				self::_page_timer(),
				self::_page_memory()
			),
			sprintf(
				'Generiert: %s zuvor',
				human_time_diff( $meta['time'], current_time( 'timestamp' ) )
			)
		);
	}

	/**
	 * Return query count
	 *
	 * @since   0.1
	 * @change  2.0
	 *
	 * @return  integer  Numbe rof queries
	 */
	private static function _page_queries() {
		return $GLOBALS['wpdb']->num_queries;
	}

	/**
	 * REturn execution time
	 *
	 * @since   0.1
	 * @change  2.0
	 *
	 * @return  integer  Execution time in seconds
	 */
	private static function _page_timer() {
		return timer_stop( 0, 2 );
	}

	/**
	 * Return memory consumption
	 *
	 * @since   0.7
	 * @change  2.0
	 *
	 * @return  string  Formatted memory size
	 */
	private static function _page_memory() {
		return ( function_exists( 'memory_get_usage' ) ? size_format( memory_get_usage(), 2 ) : 0 );
	}
}
