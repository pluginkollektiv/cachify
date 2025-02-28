<?php
/**
 * Class for HDD based caching.
 *
 * @package Cachify
 */

/* Quit */
defined( 'ABSPATH' ) || exit;

/**
 * Cachify_HDD
 */
final class Cachify_HDD implements Cachify_Backend {

	/**
	 * Availability check
	 *
	 * @return bool TRUE when installed
	 *
	 * @since 2.0.7
	 */
	public static function is_available(): bool {
		$option = get_option( 'permalink_structure' );
		return ! empty( $option );
	}

	/**
	 * Returns if gzip file creation is enabled
	 *
	 * @return bool
	 *
	 * @since 2.4.0
	 */
	public static function is_gzip_enabled(): bool {
		if ( ! function_exists( 'gzencode' ) ) {
			// GZip is not available on the system.
			return false;
		}

		/**
		 * Filter that allows to enable/disable gzip file creation
		 *
		 * @param bool $create_gzip_files Whether to create gzip files. Default is `true`
		 */
		return apply_filters( 'cachify_create_gzip_files', true );
	}

	/**
	 * Caching method as string
	 *
	 * @return string Caching method
	 *
	 * @since 2.1.2
	 */
	public static function stringify_method(): string {
		return 'HDD';
	}

	/**
	 * Store item in cache
	 *
	 * @param string $hash       Hash  of the entry [ignored].
	 * @param string $data       Content of the entry.
	 * @param int    $lifetime   Lifetime of the entry [ignored].
	 * @param bool   $sig_detail Show details in signature.
	 *
	 * @since 2.0
	 * @since 2.3.0 added $sig_details parameter
	 */
	public static function store_item( string $hash, string $data, int $lifetime, bool $sig_detail ): void {
		/* Do not store empty data. */
		if ( empty( $data ) ) {
			trigger_error( __METHOD__ . ': Empty input.', E_USER_WARNING );
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
	 * @param string $hash Hash of the entry.
	 * @return bool True if cache is present.
	 *
	 * @since 2.0
	 */
	public static function get_item( string $hash ) {
		return is_readable(
			self::_file_html()
		);
	}

	/**
	 * Delete item from cache
	 *
	 * @param string $hash Hash of the entry [ignored].
	 * @param string $url  URL of the entry.
	 *
	 * @since 2.0
	 */
	public static function delete_item( string $hash, string $url ): void {
		self::_clear_dir(
			self::_file_path( $url )
		);
	}

	/**
	 * Clear the cache
	 *
	 * @since 2.0
	 */
	public static function clear_cache(): void {
		self::_clear_dir(
			CACHIFY_CACHE_DIR,
			true
		);
	}

	/**
	 * Print the cache
	 *
	 * @param bool  $sig_detail Show details in signature.
	 * @param array $cache      Array of cache values.
	 *
	 * @since 2.0
	 */
	public static function print_cache( bool $sig_detail, $cache ): void {
		$filename = self::_file_html();
		$size     = is_readable( $filename ) ? readfile( $filename ) : false;

		if ( ! empty( $size ) ) {
			/* Ok, cache file has been sent to output. */
			exit;
		}
	}

	/**
	 * Get the cache size
	 *
	 * @return int Directory size
	 *
	 * @since 2.0
	 */
	public static function get_stats(): int {
		return self::_dir_size( CACHIFY_CACHE_DIR );
	}

	/**
	 * Generate signature
	 *
	 * @param bool $detail Show details in signature.
	 *
	 * @return string Signature string
	 *
	 * @since 2.0
	 * @since 2.3.0 added $detail parameter
	 */
	private static function _cache_signature( bool $detail ): string {
		return sprintf(
			"\n\n<!-- %s\n%s @ %s -->",
			'Cachify | https://cachify.pluginkollektiv.org',
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
	 * @param string $data Cache content.
	 *
	 * @since 2.0
	 */
	private static function _create_files( string $data ): void {
		$file_path = self::_file_path();

		/* Create directory */
		if ( ! wp_mkdir_p( $file_path ) ) {
			trigger_error( esc_html( __METHOD__ . ": Unable to create directory {$file_path}." ), E_USER_WARNING );
			return;
		}
		/* Write to file */
		self::_create_file( self::_file_html( $file_path ), $data );

		/**
		 * Filter that allows to enable/disable gzip file creation
		 *
		 * @param bool $create_gzip_files Whether to create gzip files. Default is `true`
		 */
		if ( self::is_gzip_enabled() ) {
			self::_create_file( self::_file_gzip( $file_path ), gzencode( $data, 9 ) );
		}
	}

	/**
	 * Create cache file
	 *
	 * @param string $file Path to cache file.
	 * @param string $data Cache content.
	 *
	 * @since 2.0
	 */
	private static function _create_file( string $file, string $data ): void {
		/* Writable? */
		$handle = @fopen( $file, 'wb' );
		if ( ! $handle ) {
			trigger_error( esc_html( __METHOD__ . ": Could not write file {$file}." ), E_USER_WARNING );
			return;
		}

		/* Write */
		fwrite( $handle, $data );
		fclose( $handle );
		clearstatcache();

		/* Permissions */
		$stat  = @stat( dirname( $file ) );
		$perms = $stat['mode'] & 0007777;
		$perms = $perms & 0000666;
		@chmod( $file, $perms );
		clearstatcache();
	}

	/**
	 * Clear directory
	 *
	 * @param string $dir       Directory path.
	 * @param bool   $recursive true for clearing subdirectories as well.
	 *
	 * @since 2.0
	 */
	private static function _clear_dir( string $dir, bool $recursive = false ): void {
		// Remove trailing slash.
		$dir = untrailingslashit( $dir );

		// Is directory?
		if ( ! is_dir( $dir ) ) {
			return;
		}

		// List directory contents.
		$objects = array_diff(
			scandir( $dir ),
			array( '..', '.' )
		);

		// Loop over items.
		foreach ( $objects as $object ) {
			// Expand path.
			$object = $dir . DIRECTORY_SEPARATOR . $object;

			if ( is_dir( $object ) ) {
				if ( $recursive ) {
					// Recursively clear the directory.
					self::_clear_dir( $object, $recursive );
				} elseif ( self::_user_can_delete( $object ) && 0 === count( glob( trailingslashit( $object ) . '*' ) ) ) {
					// Delete the directory, if empty.
					@rmdir( $object );
				}
			} elseif ( self::_user_can_delete( $object ) ) {
				// Delete the file.
				unlink( $object );
			}
		}

		// Remove directory, if empty.
		if ( self::_user_can_delete( $dir ) && 0 === count( glob( trailingslashit( $dir ) . '*' ) ) ) {
			@rmdir( $dir );
		}

		// Clean up.
		clearstatcache();
	}

	/**
	 * Get directory size
	 *
	 * @param string $dir Directory path.
	 *
	 * @return int|false Directory size
	 *
	 * @since 2.0
	 */
	public static function _dir_size( string $dir = '.' ) {
		/* Is directory? */
		if ( ! is_dir( $dir ) ) {
			return false;
		}

		/* Read */
		$objects = array_diff(
			scandir( $dir ),
			array( '..', '.' )
		);

		/* Empty? */
		if ( empty( $objects ) ) {
			return false;
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
	 * @param string|null $path Request URI or permalink [optional].
	 *
	 * @return string Path to cache file
	 *
	 * @since 2.0
	 */
	private static function _file_path( ?string $path = null ): string {
		$prefix = is_ssl() ? 'https-' : '';

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.InputNotValidated
		$path_parts = wp_parse_url( $path ? $path : wp_unslash( $_SERVER['REQUEST_URI'] ) );

		$path = sprintf(
			'%s%s%s%s%s',
			CACHIFY_CACHE_DIR,
			DIRECTORY_SEPARATOR,
			$prefix,
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.InputNotValidated
			strtolower( wp_unslash( $_SERVER['HTTP_HOST'] ) ),
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
	 * @param string $file_path File path [optional].
	 *
	 * @return string Path to HTML file
	 *
	 * @since 2.0
	 */
	private static function _file_html( string $file_path = '' ): string {
		return ( empty( $file_path ) ? self::_file_path() : $file_path ) . 'index.html';
	}

	/**
	 * Path to GZIP file
	 *
	 * @param string $file_path File path [optional].
	 *
	 * @return string Path to GZIP file
	 *
	 * @since 2.0
	 */
	private static function _file_gzip( string $file_path = '' ): string {
		return ( empty( $file_path ) ? self::_file_path() : $file_path ) . 'index.html.gz';
	}

	/**
	 * Does the user has the right to delete this file?
	 *
	 * @param string $file the file name.
	 *
	 * @return bool
	 */
	private static function _user_can_delete( string $file ): bool {
		if ( ! is_file( $file ) && ! is_dir( $file ) ) {
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

		$ssl_prefix   = is_ssl() ? 'https-' : '';
		$current_blog = get_blog_details( get_current_blog_id() );
		$blog_path    = CACHIFY_CACHE_DIR . DIRECTORY_SEPARATOR . $ssl_prefix . $current_blog->domain . $current_blog->path;

		if ( 0 !== strpos( $file, $blog_path ) ) {
			return false;
		}

		// We are on a subdirectory installation and the current blog is in a subdirectory.
		if ( '/' !== $current_blog->path ) {
			return true;
		}

		// If we are on the root blog in a subdirectory multisite, we check if the current dir is the root dir.
		$root_site_dir = CACHIFY_CACHE_DIR . DIRECTORY_SEPARATOR . $ssl_prefix . DOMAIN_CURRENT_SITE . DIRECTORY_SEPARATOR;
		if ( $root_site_dir === $file ) {
			return false;
		}

		// If we are on the root blog in a subdirectory multisite, we check, if the current file is part of another blog.
		global $wpdb;
		$results = $wpdb->get_col(
			$wpdb->prepare(
				'select path from ' . $wpdb->base_prefix . 'blogs where domain = %s && blog_id != %d',
				$current_blog->domain,
				$current_blog->blog_id
			)
		);
		foreach ( $results as $site ) {
			$forbidden_path = CACHIFY_CACHE_DIR . DIRECTORY_SEPARATOR . $ssl_prefix . $current_blog->domain . $site;
			if ( 0 === strpos( $file, $forbidden_path ) ) {
				return false;
			}
		}

		return true;
	}
}
