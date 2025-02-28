<?php
/**
 * Class for Memcached based caching.
 *
 * @package Cachify
 */

/* Quit */
defined( 'ABSPATH' ) || exit;

/**
 * Cachify_MEMCACHED
 */
final class Cachify_MEMCACHED implements Cachify_Backend {

	/**
	 * Memcached-Object
	 *
	 * @var object
	 *
	 * @since 2.0.7
	 */
	private static $_memcached;

	/**
	 * Availability check
	 *
	 * @return bool TRUE when installed
	 *
	 * @since 2.0.7
	 */
	public static function is_available(): bool {
		return class_exists( 'Memcached' )
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			&& isset( $_SERVER['SERVER_SOFTWARE'] ) && strpos( strtolower( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ), 'nginx' ) !== false;
	}

	/**
	 * Caching method as string
	 *
	 * @return string Caching method
	 *
	 * @since 2.1.2
	 */
	public static function stringify_method(): string {
		return 'Memcached';
	}

	/**
	 * Store item in cache
	 *
	 * @param string $hash       Hash of the entry [ignored].
	 * @param string $data       Content of the entry.
	 * @param int    $lifetime   Lifetime of the entry.
	 * @param bool   $sig_detail Show details in signature.
	 *
	 * @since 2.0.7
	 * @since 2.3.0 added $sig_detail parameter
	 */
	public static function store_item( string $hash, string $data, int $lifetime, bool $sig_detail ): void {
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
	 * @param string $hash Hash of the entry.
	 *
	 * @return mixed Content of the entry
	 *
	 * @since 2.0.7
	 */
	public static function get_item( string $hash ) {
		/* Server connect */
		if ( ! self::_connect_server() ) {
			return null;
		}

		/* Get item */
		return self::$_memcached->get(
			self::_file_path()
		);
	}

	/**
	 * Delete item from cache
	 *
	 * @param string $hash Hash of the entry.
	 * @param string $url  URL of the entry [optional].
	 *
	 * @since 2.0.7
	 */
	public static function delete_item( string $hash, string $url = '' ): void {
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
	 * @since 2.0.7
	 */
	public static function clear_cache(): void {
		/* Server connect */
		if ( ! self::_connect_server() ) {
			return;
		}

		if ( ! self::$_memcached instanceof Memcached ) {
			return;
		}

		/* Flush */
		self::$_memcached->flush();
	}

	/**
	 * Print the cache
	 *
	 * @param bool  $sig_detail Show details in signature.
	 * @param array $cache      Array of cache values.
	 *
	 * @since 2.0.7
	 */
	public static function print_cache( bool $sig_detail, $cache ): void {
		// Not supported.
	}

	/**
	 * Get the cache size
	 *
	 * @return mixed Cache size
	 *
	 * @since 2.0.7
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
	 * @param bool $detail Show details in signature.
	 *
	 * @return string Signature string
	 *
	 * @since 2.0.7
	 * @since 2.3.0 added $detail parameter
	 */
	private static function _cache_signature( bool $detail ): string {
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
	 * @param string|null $path Request URI or permalink [optional].
	 *
	 * @return string Path to cache file
	 *
	 * @since 2.0.7
	 */
	private static function _file_path( ?string $path = null ): string {
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
	 * @hook  array  cachify_memcached_servers  Array with memcached servers
	 *
	 * @return bool TRUE on success
	 *
	 * @since 2.0.7
	 */
	private static function _connect_server(): bool {
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
					Memcached::OPT_COMPRESSION     => false,
					Memcached::OPT_BUFFER_WRITES   => true,
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
