<?php

/**
* Cachify_CLI
*/
final class Cachify_CLI {

  /**
	 * Flush Cache Callback
	 *
	 * @since   2.3.0
	 * @change  2.3.0
	 *
	 * @param array $args
	 * @param array $assoc_args
	 */
  public static function flush_cache( $args, $assoc_args ){

    // set default args
    $assoc_args = wp_parse_args( $assoc_args, array( 'all-methods' => false ) );

    Cachify::flush_total_cache( $all_methods );

    WP_CLI::success( "Cache flushed" );

  }

  /**
	 * Get cache size
	 *
	 * @since   2.3.0
	 * @change  2.3.0
	 *
	 * @param array $args
	 * @param array $assoc_args
	 */
  public static function get_cache_size( $args, $assoc_args ){

    // set default args
    $assoc_args = wp_parse_args( $assoc_args, array( 'raw' => false ) );

    // get cache size
    $cache_size = Cachify::get_cache_size();

    if( $assoc_args["raw"] ){
      $message = $cache_size;
    }else{
      $message = "The cache size is $cache_size bytes";
    }

    WP_CLI::line( $message );

  }

  /**
   * Register CLI Commands
   *
   * @since   2.3.0
   * @change  2.3.0
   */
  public static function add_commands() {
    if ( defined( 'WP_CLI' ) && WP_CLI ) {

      /*
       * Add flush command
       */
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
                    'type'     => 'flag',
                    'name'     => 'all-methods',
                    'description'   => 'Flush all caching methods',
                    'optional' => true,
                )
            ),
        )
      );

      /*
       * Add cache-size command
       */
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
                    'type'     => 'flag',
                    'name'     => 'raw',
                    'description'   => 'Raw size output in bytes',
                    'optional' => true,
                ),
            ),
        )
      );

    }
  }
}
