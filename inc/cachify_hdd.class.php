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
			trigger_error( __METHOD__ . ": Empty input.", E_USER_WARNING );
			return;
		}

		/* Store data */
		self::_create_files(
			$data . self::_cache_signature( $sig_detail )
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
	 * @param   string $hash  Hash of the entry [ignored].
	 * @param   string $url   URL of the entry.
	 */
	public static function delete_item( $hash, $url ) {
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
		$filename = self::_file_html();
		$size = is_readable( $filename ) ? readfile( $filename ) : false;
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
	 * @change  2.3.0
	 *
	 * @param   bool $detail  Show details in signature.
	 * @return  string        Signature string
	 */
	private static function _cache_signature( $detail ) {
		return sprintf(
			"\n\n<!-- %s\n%s @ %s -->",
			'Cachify | http://cachify.de',
			( $detail ? 'HDD Cache' : __( 'Generated', 'cachify' ) ),
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
		$file_path = self::_file_path();

		/* Create directory */
		if ( ! wp_mkdir_p( $file_path ) ) {
			trigger_error( __METHOD__ . ": Unable to create directory {$file_path}.", E_USER_WARNING );
			return;
		}

		/* Write to file */
		self::_create_file( self::_file_html( $file_path ), $data );
		self::_create_file( self::_file_gzip( $file_path ), gzencode( $data, 9 ) );
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
			trigger_error( __METHOD__ . ": Could not write file {$file}.", E_USER_WARNING );
			return;
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
				if ( self::_user_can_delete( $object ) ) {
					unlink( $object );
				}
			}
		}

		/* Remove directory */
		if ( $recursive ) {
			if ( self::_user_can_delete( $dir ) && 0 === count( glob( trailingslashit( $dir ) . '*' ) ) ) {
				@rmdir( $dir );
			}
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

		$path_parts = wp_parse_url( $path ? $path : $_SERVER['REQUEST_URI'] );

		$path = sprintf(
			'%s%s%s%s%s',
			CACHIFY_CACHE_DIR,
			DIRECTORY_SEPARATOR,
			$prefix,
			strtolower( $_SERVER['HTTP_HOST'] ),
			$path_parts['path']
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
	 * @change  2.3.0
	 *
	 * @param   string $file_path   File path [optional].
	 * @return  string              Path to HTML file
	 */
	private static function _file_html( $file_path = '' ) {
		return ( empty( $file_path ) ? self::_file_path() : $file_path ) . 'index.html';
	}

	/**
	 * Path to GZIP file
	 *
	 * @since   2.0
	 * @change  2.3.0
	 *
	 * @param   string $file_path   File path [optional].
	 * @return  string              Path to GZIP file
	 */
	private static function _file_gzip( $file_path = '' ) {
		return ( empty( $file_path ) ? self::_file_path() : $file_path )  . 'index.html.gz';
	}

	/**
	 * Does the user has the right to delete this file?
	 *
	 * @param string $file
	 *
	 * @return bool
	 */
	private static function _user_can_delete( $file ) {

		if ( ! is_file( $file ) && ! is_dir( $file ) ) {
			return false;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		if ( 0 !== strpos( $file, CACHIFY_CACHE_DIR ) ) {
			return false;
		}

		// If its just a single blog, the user has the right to delete this file.
		// But also, if you are in the network admin, you should be able to delete all files.
		if ( ! is_multisite() || is_network_admin() ) {
			return true;
		}

		if ( is_dir( $file ) ) {
			$file = trailingslashit( $file );
		}

		$ssl_prefix = is_ssl() ? 'https-' : '';
		$current_blog = get_blog_details( get_current_blog_id() );
		$blog_path = CACHIFY_CACHE_DIR . DIRECTORY_SEPARATOR . $ssl_prefix . $current_blog->domain . $current_blog->path;

		if ( 0 !== strpos( $file, $blog_path ) ) {
			return false;
		}

		// We are on a subdirectory installation and the current blog is in a subdirectory
		if ( '/' !== $current_blog->path ) {
			return true;
		}

		// If we are on the root blog in a subdirectory multisite, we check, if the current file
		// is part of another blog.
		global $wpdb;
		$sql = $wpdb->prepare(
			'select path from ' . $wpdb->base_prefix . 'blogs where domain = %s && blog_id != %d',
			$current_blog->domain,
			$current_blog->blog_id
		);
		$results = $wpdb->get_col( $sql );
		foreach ( $results as $site ) {
			$forbidden_path = CACHIFY_CACHE_DIR . DIRECTORY_SEPARATOR . $ssl_prefix . $current_blog->domain . $site;
			if ( 0 === strpos( $file, $forbidden_path ) ) {
				return false;
			}
		}

		return true;

	}
}
