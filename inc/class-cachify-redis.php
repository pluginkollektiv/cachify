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
	private static $_redis;

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
	 * @param string $hash Hash of the entry.
	 * @return mixed Content of the entry
	 */
	public static function get_item( string $hash ) {
		/* Server connect */
		if ( ! self::_connect_server() ) {
			return null;
		}

		/* Get item */
		return self::$_redis->get(
			self::_file_path()
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
	 * @return void
	 */
	public static function clear_cache(): void {
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
	public static function print_cache( bool $sig_detail, $cache ): void {
		echo $cache;    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		exit;
	}

	/**
	 * Get the cache size
	 *
	 * @return integer Directory size
	 */
	public static function get_stats(): ?int {
		/* Server connect */
		if ( ! self::_connect_server() ) {
			return null;
		}

		/* Info */
		$data = self::$_redis->info( 'MEMORY' );

		/* No stats? */
		if ( empty( $data ) ) {
			return null;
		}

		/* Empty */
		if ( empty( $data['used_memory_dataset'] ) ) {
			return null;
		}

		return $data['used_memory_dataset'];
	}

	/**
	 * Generate signature
	 *
	 * @param bool $detail Show details in signature.
	 * @return string Signature string
	 */
	private static function _cache_signature( bool $detail ): string {
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
	 * Connect to Redis server
	 *
	 * @return boolean TRUE on success
	 */
	private static function _connect_server(): bool {
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
			self::$_redis->connect( ...$con );

			if ( ! self::$_redis->isConnected() ) {
				return false;
			}
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
