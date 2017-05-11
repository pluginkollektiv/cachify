<?php

/**
* Cachify_HDD
*/
final class Cachify_HDD {

	/**
	 * Availability check
	 *
	 * @since   2.0.7
	 * @change  2.0.7
	 *
	 * @return  boolean  true/false  TRUE when installed
	 */
	public static function is_available() {
		return ! empty( get_option( 'permalink_structure' ) );
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
		return 'HDD';
	}

	/**
	 * Store item in cache
	 *
	 * @since   2.0
	 * @change  2.0
	 *
	 * @param   string  $hash      Hash  of the entry [optional].
	 * @param   string  $data      Content of the entry.
	 * @param   integer $lifetime  Lifetime of the entry [optional].
	 */
	public static function store_item( $hash, $data, $lifetime ) {
		/* Empty? */
		if ( empty( $data ) ) {
			wp_die( 'HDD add item: Empty input.' );
		}

		/* Store data */
		self::_create_files(
			$data . self::_cache_signature()
		);
	}

	/**
	 * Read item from cache
	 *
	 * @since   2.0
	 * @change  2.0
	 *
	 * @return  boolean  True if cache is present.
	 */
	public static function get_item() {
		return is_readable(
			self::_file_html()
		);
	}

	/**
	 * Delete item from cache
	 *
	 * @since   2.0
	 * @change  2.0
	 *
	 * @param   string $hash  Hash of the entry [optional].
	 * @param   string $url   URL of the entry.
	 */
	public static function delete_item( $hash = '', $url ) {
		/* Empty? */
		if ( empty( $url ) ) {
			wp_die( 'HDD delete item: Empty input.' );
		}

		/* Delete */
		self::_clear_dir(
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
		self::_clear_dir(
			CACHIFY_CACHE_DIR,
			true
		);
	}

	/**
	 * Print the cache
	 *
	 * @since   2.0
	 * @change  2.3
	 */
	public static function print_cache() {
		$size = @readfile( self::_file_html() );
		if ( ! empty ( $size ) ) {
			/* Ok, cache file has been sent to output. */
			exit;
		}
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
		return self::_dir_size( CACHIFY_CACHE_DIR );
	}

	/**
	 * Generate signature
	 *
	 * @since   2.0
	 * @change  2.0.5
	 *
	 * @return  string  Signature string
	 */
	private static function _cache_signature() {
		return sprintf(
			"\n\n<!-- %s\n%s @ %s -->",
			'Cachify | http://cachify.de',
			'HDD Cache',
			date_i18n(
				'd.m.Y H:i:s',
				current_time( 'timestamp' )
			)
		);
	}

	/**
	 * Initialize caching process
	 *
	 * @since   2.0
	 * @change  2.0
	 *
	 * @param   string $data  Cache content.
	 */
	private static function _create_files( $data ) {
		/* Create directory */
		if ( ! wp_mkdir_p( self::_file_path() ) ) {
			wp_die( 'Unable to create directory.' );
		}

		/* Write to file */
		self::_create_file( self::_file_html(), $data );
		self::_create_file( self::_file_gzip(), gzencode( $data, 9 ) );
	}

	/**
	 * Create cache file
	 *
	 * @since   2.0
	 * @change  2.0
	 *
	 * @param   string $file  Path to cache file.
	 * @param   string $data  Cache content.
	 */
	private static function _create_file( $file, $data ) {
		/* Writable? */
		if ( ! $handle = @fopen( $file, 'wb' ) ) {
			wp_die( 'Could not write file.' );
		}

		/* Write */
		@fwrite( $handle, $data );
		fclose( $handle );
		clearstatcache();

		/* Permissions */
		$stat = @stat( dirname( $file ) );
		$perms = $stat['mode'] & 0007777;
		$perms = $perms & 0000666;
		@chmod( $file, $perms );
		clearstatcache();
	}

	/**
	 * Clear directory
	 *
	 * @since   2.0
	 * @change  2.0.5
	 *
	 * @param   string  $dir        Directory path.
	 * @param   boolean $recursive  Clear subdirectories?
	 */
	private static function _clear_dir( $dir, $recursive = false ) {
		/* Remote training slash */
		$dir = untrailingslashit( $dir );

		/* Is directory? */
		if ( ! is_dir( $dir ) ) {
			return;
		}

		/* Read */
		$objects = array_diff(
			scandir( $dir ),
			array( '..', '.' )
		);

		/* Empty? */
		if ( empty( $objects ) ) {
			return;
		}

		/* Loop over items */
		foreach ( $objects as $object ) {
			/* Expand path */
			$object = $dir . DIRECTORY_SEPARATOR . $object;

			/* Directory or file */
			if ( is_dir( $object ) && $recursive ) {
				self::_clear_dir( $object, $recursive );
			} else {
				unlink( $object );
			}
		}

		/* Remove directory */
		if ( $recursive ) {
			@rmdir( $dir );
		}

		/* Clean up */
		clearstatcache();
	}

	/**
	 * Get directory size
	 *
	 * @since   2.0
	 * @change  2.0
	 *
	 * @param   string $dir   Directory path.
	 * @return  mixed         Directory size
	 */
	public static function _dir_size( $dir = '.' ) {
		/* Is directory? */
		if ( ! is_dir( $dir ) ) {
			return;
		}

		/* Read */
		$objects = array_diff(
			scandir( $dir ),
			array( '..', '.' )
		);

		/* Empty? */
		if ( empty( $objects ) ) {
			return;
		}

		/* Init */
		$size = 0;

		/* Loop over items */
		foreach ( $objects as $object ) {
			/* Expand path */
			$object = $dir . DIRECTORY_SEPARATOR . $object;

			/* Directory or file */
			if ( is_dir( $object ) ) {
				$size += self::_dir_size( $object );
			} else {
				$size += filesize( $object );
			}
		}

		return $size;
	}

	/**
	 * Path to cache file
	 *
	 * @since   2.0
	 * @change  2.0
	 *
	 * @param   string $path  Request URI or permalink [optional].
	 * @return  string        Path to cache file
	 */
	private static function _file_path( $path = null ) {
		$prefix = is_ssl() ? 'https-' : '';

		$path = sprintf(
			'%s%s%s%s%s',
			CACHIFY_CACHE_DIR,
			DIRECTORY_SEPARATOR,
			$prefix,
			parse_url(
				'http://' . strtolower( $_SERVER['HTTP_HOST'] ),
				PHP_URL_HOST
			),
			parse_url(
				( $path ? $path : $_SERVER['REQUEST_URI'] ),
				PHP_URL_PATH
			)
		);

		if ( validate_file( $path ) > 0 ) {
			wp_die( 'Invalid file path.' );
		}

		return trailingslashit( $path );
	}

	/**
	 * Path to HTML file
	 *
	 * @since   2.0
	 * @change  2.0
	 *
	 * @return  string  Path to HTML file
	 */
	private static function _file_html() {
		return self::_file_path() . 'index.html';
	}

	/**
	 * Path to GZIP file
	 *
	 * @since   2.0
	 * @change  2.0
	 *
	 * @return  string  Path to GZIP file
	 */
	private static function _file_gzip() {
		return self::_file_path() . 'index.html.gz';
	}
}
