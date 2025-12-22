<?php
/**
 * Class for Redis based caching.
 *
 * @package Cachify
 */

/* Quit */
defined( 'ABSPATH' ) || exit;

/**
 * Cachify_REDIS class
 *
 * @since 2.4.0
 */
final class Cachify_REDIS implements Cachify_Backend {

	/**
	 * Redis-Object
	 *
	 * @var Redis|null
	 */
	private static $redis;

	/**
	 * Availability check
	 *
	 * @return boolean TRUE when installed
	 */
	public static function is_available(): bool {
		return class_exists( 'Redis' );
	}

	/**
	 * Caching method as string
	 *
	 * @return string Caching method
	 */
	public static function stringify_method(): string {
		return 'Redis';
	}

	/**
	 * Store item in cache
	 *
	 * @param string  $hash       Hash  of the entry [ignored].
	 * @param string  $data       Content of the entry.
	 * @param integer $lifetime   Lifetime of the entry [ignored].
	 * @param bool    $sig_detail Show details in signature.
	 */
	public static function store_item( string $hash, string $data, int $lifetime, bool $sig_detail ): void {
		/* Do not store empty data. */
		if ( empty( $data ) ) {
			trigger_error( __METHOD__ . ': Empty input.', E_USER_WARNING );
			return;
		}

		/* Server connect */
		if ( ! self::connect_server() ) {
			return;
		}

		/* Add item */
		self::$redis->set(
			self::file_path(),
			$data . self::cache_signature( $sig_detail ),
			$lifetime
		);
	}

	/**
	 * Read item from cache
	 *
	 * @param string $hash Hash of the entry.
	 * @return mixed Content of the entry
	 */
	public static function get_item( string $hash ) {
		/* Server connect */
		if ( ! self::connect_server() ) {
			return null;
		}

		/* Get item */
		return self::$redis->get(
			self::file_path()
		);
	}

	/**
	 * Delete item from cache
	 *
	 * @param   string $hash  Hash of the entry [ignored].
	 * @param   string $url   URL of the entry.
	 */
	public static function delete_item( string $hash, string $url ): void {
		/* Server connect */
		if ( ! self::connect_server() ) {
			return;
		}

		/* Delete */
		self::$redis->del(
			self::file_path( $url )
		);
	}

	/**
	 * Clear the cache
	 *
	 * @return void
	 */
	public static function clear_cache(): void {
		/* Server connect */
		if ( ! self::connect_server() ) {
			return;
		}

		/* Delete all cache entries for this site */

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.InputNotValidated
		$host = wp_unslash( $_SERVER['HTTP_HOST'] );
		$keys = self::$redis->keys( $host . '*' );

		if ( $keys ) {
			$prefix = self::$redis->getOption( Redis::OPT_PREFIX );

			// Strip the prefix from the keys, as phpredis' unlink() will add it again.
			$stripped_keys = array_map(
				function ( $key ) use ( $prefix ) {
					return preg_replace( "/^${prefix}/", '', $key );
				},
				$keys
			);
			self::$redis->unlink( $stripped_keys );
		}
	}

	/**
	 * Print the cache
	 *
	 * @param bool   $sig_detail  Show details in signature.
	 * @param string $cache       Cached content.
	 */
	public static function print_cache( bool $sig_detail, $cache ): void {
		echo $cache;    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		exit;
	}

	/**
	 * Get the cache size
	 *
	 * @return integer Cache size in bytes.
	 */
	public static function get_stats(): int {
		/* Server connect */
		if ( ! self::connect_server() ) {
			return 0;
		}

		/* Info */
		$data = self::$redis->info( 'MEMORY' );

		/* No stats? */
		if ( empty( $data ) ) {
			return 0;
		}

		/* Empty */
		if ( empty( $data['used_memory_dataset'] ) ) {
			return 0;
		}

		return (int) $data['used_memory_dataset'];
	}

	/**
	 * Generate signature
	 *
	 * @param bool $detail Show details in signature.
	 * @return string Signature string
	 */
	private static function cache_signature( bool $detail ): string {
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
	 * @param string|null $path Request URI or permalink [optional].
	 * @return string Path to cache file
	 */
	private static function file_path( ?string $path = null ): string {
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
	 * @return boolean TRUE on success
	 */
	private static function connect_server(): bool {
		/* Not enabled? */
		if ( ! self::is_available() ) {
			return false;
		}

		/* Have object and it thinks it's connected to a server */
		if ( is_object( self::$redis ) && self::$redis->isConnected() ) {
			return true;
		}

		/* Init */
		self::$redis = new Redis();

		/**
		 * Filter hook to adjust Redis connection parameters
		 *
		 * @param array $redis_server Redis connection parameters.
		 *
		 * @see   Redis::connect() For supported parameters.
		 *
		 * @since 2.4.0
		 */
		$con = apply_filters( 'cachify_redis_servers', array( 'localhost' ) );
		$con = self::sanitize_con_parameters( $con );

		if ( false === $con ) {
			return false;
		}

		// Establish connection.
		try {
			self::$redis->connect( ...$con );

			if ( ! self::$redis->isConnected() ) {
				return false;
			}

			// Automatically prefix the Redis keys for all operations.
			self::$redis->setOption( Redis::OPT_PREFIX, 'cachify:' );
		} catch ( Exception $e ) {
			return false;
		}

		return true;
	}

	/**
	 * Sanitize Redis connection parameters.
	 *
	 * @param mixed $con Connection parameters (from hook).
	 *
	 * @return array|false Array of connection arguments or FALSE, if invalid.
	 */
	private static function sanitize_con_parameters( $con ) {
		if ( is_string( $con ) ) {
			return array( $con );
		} elseif ( is_array( $con ) && ! empty( $con ) ) {
			$con[0] = strval( $con[0] );  // Host or socket path.
			if ( count( $con ) > 1 ) {
				$con[1] = intval( $con[1] );  // Port number.
			}
			if ( count( $con ) > 2 ) {
				$con[2] = floatval( $con[2] );  // Socket timeout in seconds.
			}
			if ( count( $con ) > 3 && ! is_null( $con[3] ) ) {
				$con[3] = strval( $con[3] );  // Persistent connection ID.
			}
			if ( count( $con ) > 4 ) {
				$con[4] = intval( $con[4] );  // Retry interval in milliseconds.
			}
			if ( count( $con ) > 5 ) {
				$con[5] = floatval( $con[5] );  // Read timeout in seconds.
			}
			if ( count( $con ) > 6 && ! is_null( $con[6] ) && ! is_array( $con[6] ) ) {
				return false;  // Context parameters, e.g. authentication (since PhpRedis 5.3).
			}
			if ( count( $con ) > 7 ) {
				$con = array_slice( $con, 0, 7 );  // Trim excessive parameters.
			}

			return $con;
		} else {
			return false;
		}
	}
}
