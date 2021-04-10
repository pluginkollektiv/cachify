<?php
/**
 * Class for Memcached based caching.
 *
 * @package Cachify
 */

/**
 * Cachify_MEMCACHED
 */
final class Cachify_MEMCACHED {

	/**
	 * Memcached-Object
	 *
	 * @since  2.0.7
	 * @var    object
	 */
	private static $_memcached;

	/**
	 * Availability check
	 *
	 * @since   2.0.7
	 * @change  2.0.7
	 *
	 * @return  boolean  true/false  TRUE when installed
	 */
	public static function is_available() {
		return class_exists( 'Memcached' )
		       // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			   && isset( $_SERVER['SERVER_SOFTWARE'] ) && strpos( strtolower( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ), 'nginx' ) !== false;
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
		return 'Memcached';
	}

	/**
	 * Store item in cache
	 *
	 * @param   string  $hash       Hash of the entry [ignored].
	 * @param   string  $data       Content of the entry.
	 * @param   integer $lifetime   Lifetime of the entry.
	 * @param   bool    $sig_detail  Show details in signature.
	 *
	 * @since   2.0.7
	 * @change  2.3.0
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
		self::$_memcached->set(
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
		return self::$_memcached->get(
			self::_file_path()
		);
	}

	/**
	 * Delete item from cache
	 *
	 * @since   2.0.7
	 * @change  2.0.7
	 *
	 * @param   string $hash  Hash of the entry.
	 * @param   string $url   URL of the entry [optional].
	 */
	public static function delete_item( $hash, $url = '' ) {
		/* Server connect */
		if ( ! self::_connect_server() ) {
			return;
		}

		/* Delete */
		self::$_memcached->delete(
			self::_file_path( $url )
		);
	}

	/**
	 * Clear the cache
	 *
	 * @since   2.0.7
	 * @change  2.0.7
	 */
	public static function clear_cache() {
		/* Server connect */
		if ( ! self::_connect_server() ) {
			return;
		}

		/* Flush */
		@self::$_memcached->flush();
	}

	/**
	 * Print the cache
	 *
	 * @since   2.0.7
	 * @change  2.0.7
	 */
	public static function print_cache() {
		return;
	}

	/**
	 * Get the cache size
	 *
	 * @since   2.0.7
	 * @change  2.0.7
	 *
	 * @return  mixed  Cache size
	 */
	public static function get_stats() {
		/* Server connect */
		if ( ! self::_connect_server() ) {
			return null;
		}

		/* Info */
		$data = self::$_memcached->getStats();

		/* No stats? */
		if ( empty( $data ) ) {
			return null;
		}

		/* Get first key */
		$data = $data[ key( $data ) ];

		/* Empty */
		if ( empty( $data['bytes'] ) ) {
			return null;
		}

		return $data['bytes'];
	}

	/**
	 * Generate signature
	 *
	 * @since   2.0.7
	 * @change  2.3.0
	 *
	 * @param   bool $detail  Show details in signature.
	 * @return  string        Signature string
	 */
	private static function _cache_signature( $detail ) {
		return sprintf(
			"\n\n<!-- %s\n%s @ %s -->",
			'Cachify | https://cachify.pluginkollektiv.org',
			( $detail ? 'Memcached' : __( 'Generated', 'cachify' ) ),
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
	 * Connect to Memcached server
	 *
	 * @since   2.0.7
	 * @change  2.1.8
	 *
	 * @hook    array  cachify_memcached_servers  Array with memcached servers
	 *
	 * @return  boolean  true/false  TRUE on success
	 */
	private static function _connect_server() {
		/* Not enabled? */
		if ( ! self::is_available() ) {
			return false;
		}

		/* Already connected */
		if ( is_object( self::$_memcached ) ) {
			return true;
		}

		/* Init */
		self::$_memcached = new Memcached();

		/* Set options */
		if ( defined( 'HHVM_VERSION' ) ) {
			self::$_memcached->setOption( Memcached::OPT_COMPRESSION, false );
			self::$_memcached->setOption( Memcached::OPT_BUFFER_WRITES, true );
			self::$_memcached->setOption( Memcached::OPT_BINARY_PROTOCOL, true );
		} else {
			self::$_memcached->setOptions(
				array(
					Memcached::OPT_COMPRESSION => false,
					Memcached::OPT_BUFFER_WRITES => true,
					Memcached::OPT_BINARY_PROTOCOL => true,
				)
			);
		}

		/* Connect */
		self::$_memcached->addServers(
			(array) apply_filters(
				'cachify_memcached_servers',
				array(
					array(
						'127.0.0.1',
						11211,
					),
				)
			)
		);

		return true;
	}
}
