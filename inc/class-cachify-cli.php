<?php
/**
 * Class for the command line interface (WP CLI commands).
 *
 * @package Cachify
 */

/* Quit */
defined( 'ABSPATH' ) || exit;

/**
 * Cachify_CLI
 */
final class Cachify_CLI {

	/**
	 * Flush Cache Callback
	 *
	 * @param array $args the CLI arguments as array.
	 * @param array $assoc_args the CLI arguments as associative array.
	 *
	 * @since 2.3.0
	 * @since 2.5.0 Added the network argument.
	 */
	public static function flush_cache( array $args, array $assoc_args ): void {
		// Set default arguments.
		$assoc_args = wp_parse_args(
			$assoc_args,
			array(
				'all-methods' => false,
				'network'    => false,
			)
		);

		if ( $assoc_args['network'] ) {
			if ( ! is_multisite() ) {
				WP_CLI::error( 'The --network flag requires a multisite installation.' );
			}

			$flushed               = 0;
			$original_host         = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '';
			$original_request_uri  = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '/';
			$original_server_name  = isset( $_SERVER['SERVER_NAME'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_NAME'] ) ) : $original_host;

			try {
				foreach (
					get_sites(
						array(
							'fields' => 'ids',
							'number' => 0,
						)
					)
					as $site_id
				) {
					switch_to_blog( (int) $site_id );
					try {
						$site         = get_site( (int) $site_id );
						$site_host    = ( $site && ! empty( $site->domain ) ) ? $site->domain : $original_host;
						$site_request = '/' . ltrim( ( $site && ! empty( $site->path ) ) ? $site->path : '/', '/' );

						$_SERVER['HTTP_HOST']   = $site_host;
						$_SERVER['REQUEST_URI']  = $site_request;
						$_SERVER['SERVER_NAME']  = $site_host;

						Cachify::init();
						Cachify::flush_total_cache( $assoc_args['all-methods'] );
						++$flushed;
					} finally {
						restore_current_blog();
						$_SERVER['HTTP_HOST']  = $original_host;
						$_SERVER['REQUEST_URI'] = $original_request_uri;
						$_SERVER['SERVER_NAME'] = $original_server_name;
					}
				}
			} finally {
				Cachify::init();
			}

			if ( $assoc_args['all-methods'] ) {
				WP_CLI::success( sprintf( 'All Cachify caches flushed on %d sites', $flushed ) );
			} else {
				WP_CLI::success( sprintf( 'Cachify cache flushed on %d sites', $flushed ) );
			}

			return;
		}

		Cachify::flush_total_cache( $assoc_args['all-methods'] );

		if ( $assoc_args['all-methods'] ) {
			WP_CLI::success( 'All Cachify caches flushed' );
		} else {
			WP_CLI::success( 'Cachify cache flushed' );
		}
	}

	/**
	 * Get cache size
	 *
	 * @param array $args the CLI arguments as array.
	 * @param array $assoc_args the CLI arguments as associative array.
	 *
	 * @since 2.3.0
	 */
	public static function get_cache_size( array $args, array $assoc_args ): void {
		// Set default arguments.
		$assoc_args = wp_parse_args( $assoc_args, array( 'raw' => false ) );

		$cache_size = Cachify::get_cache_size();

		if ( $assoc_args['raw'] ) {
			$message = $cache_size;
		} else {
			$message = "The cache size is $cache_size bytes";
		}

		WP_CLI::line( $message );
	}

	/**
	 * Register CLI Commands
	 *
	 * @since 2.3.0
	 */
	public static function add_commands(): void {
		// Add flush command.
		WP_CLI::add_command(
			'cachify flush',
			array(
				'Cachify_CLI',
				'flush_cache',
			),
			array(
				'shortdesc' => 'Flush site cache',
				'synopsis'  => array(
					array(
						'type'        => 'flag',
						'name'        => 'all-methods',
						'description' => 'Flush all caching methods',
						'optional'    => true,
					),
					array(
						'type'        => 'flag',
						'name'        => 'network',
						'description' => 'Flush cache for every site in the network',
						'optional'    => true,
					),
				),
			)
		);

		// Add cache-size command.
		WP_CLI::add_command(
			'cachify cache-size',
			array(
				'Cachify_CLI',
				'get_cache_size',
			),
			array(
				'shortdesc' => 'Get the size of the cache in bytes',
				'synopsis'  => array(
					array(
						'type'        => 'flag',
						'name'        => 'raw',
						'description' => 'Raw size output in bytes',
						'optional'    => true,
					),
				),
			)
		);
	}
}
