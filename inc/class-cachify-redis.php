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
	 * Redis-Object
	 *
	 * @since  2.0.7
	 * @var    object
	 */
	private static $_redis;

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
		return class_exists( 'Redis' )
			   && getenv( 'REDIS_HOST' )
			   && getenv( 'REDIS_PORT' );
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
		/* Do not store empty data. */
		if ( empty( $data ) ) {
			trigger_error( __METHOD__ . ': Empty input.', E_USER_WARNING );
			return;
		}

		/* Server connect */
		if ( ! self::_connect_server() ) {
			return;
		}

		/* Add item */
		self::$_redis->set(
			self::_file_path(),
			$data . self::_cache_signature( $sig_detail ),
			$lifetime
		);
	}

	/**
	 * Read item from cache
	 *
	 * @since   2.0.7
	 * @change  2.0.7
	 *
	 * @param   string $hash  Hash of the entry.
	 * @return  mixed         Content of the entry
	 */
	public static function get_item( $hash ) {
		/* Server connect */
		if ( ! self::_connect_server() ) {
			return;
		}

		/* Get item */
		return self::$_redis->get(
			self::_file_path()
		);
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
		/* Server connect */
		if ( ! self::_connect_server() ) {
			return;
		}

		/* Delete */
		self::$_redis->del(
			self::_file_path( $url )
		);
	}

	/**
	 * Clear the cache
	 *
	 * @since   2.0
	 * @change  2.0
	 */
	public static function clear_cache() {
		/* Server connect */
		if ( ! self::_connect_server() ) {
			return;
		}

		/* Flush */
		@self::$_redis->flushAll();
	}

	/**
	 * Print the cache
	 *
	 * @param bool   $sig_detail  Show details in signature.
	 * @param string $cache       Cached content.
	 */
	public static function print_cache( $sig_detail, $cache ) {
		echo $cache;    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		exit;
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
		/* Server connect */
		if ( ! self::_connect_server() ) {
			return null;
		}

		/* Info */
		$data = self::$_redis->info();

		/* No stats? */
		if ( empty( $data ) ) {
			return null;
		}

		/* Empty */
		if ( empty( $data['used_memory'] ) ) {
			return null;
		}

		return $data['used_memory'];
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

	/**
	 * Path of cache file
	 *
	 * @since   2.0.7
	 * @change  2.0.7
	 *
	 * @param   string $path  Request URI or permalink [optional].
	 * @return  string        Path to cache file
	 */
	private static function _file_path( $path = null ) {
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.InputNotValidated
		$path_parts = wp_parse_url( $path ? $path : wp_unslash( $_SERVER['REQUEST_URI'] ) );

		return trailingslashit(
			sprintf(
				'%s%s',
				// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.InputNotValidated
				wp_unslash( $_SERVER['HTTP_HOST'] ),
				$path_parts['path']
			)
		);
	}

	/**
	 * Connect to Redis server
	 *
	 * @return  boolean  true/false  TRUE on success
	 */
	private static function _connect_server() {
		/* Not enabled? */
		if ( ! self::is_available() ) {
			return false;
		}

		/* Have object and it thinks it's connected to a server */
		if ( is_object( self::$_redis ) && self::$_redis->isConnected() ) {
			return true;
		}

		/* Init */
		self::$_redis = new Redis();

		/* Set options & connect */
		try {
			self::$_redis->connect( getenv( 'REDIS_HOST' ), intval( getenv( 'REDIS_PORT' ) ) );
		} catch ( Exception $e ) {
			return false;
		}

		if ( ! self::$_redis->isConnected() ) {
			return false;
		}

		return true;
	}

}
