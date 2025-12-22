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
	 */
	public static function flush_cache( array $args, array $assoc_args ): void {
		// Set default arguments.
		$assoc_args = wp_parse_args( $assoc_args, array( 'all-methods' => false ) );

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
