<?php

/**
* Cachify_CLI
*/
final class Cachify_CLI {

  /**
	 * Flush Cache
	 *
	 * @since   2.3.0
	 * @change  2.3.0
	 *
	 * @param array $args
	 * @param array $assoc_args
	 */
  public static function flush_cache( $args, $assoc_args ){

    // set default args
    $assoc_args = wp_parse_args( $assoc_args, array( 'all-methods' => false, 'ids' => null, 'page-url' => null ) );

    if( $assoc_args['ids'] ){

      // convert id list to array
      $ids = explode( ',', $assoc_args['ids'] );

      foreach ( $ids as $id ) {
        Cachify::remove_page_cache_by_post_id( $id );
        WP_CLI::line( 'Cache flushed for ID ' . $id );
      }

    }elseif ( $assoc_args['page-url'] ) {

      Cachify::remove_page_cache_by_url( $assoc_args['page-url'] );
      WP_CLI::line( 'Cache flushed for URL ' . $assoc_args['page-url'] );

    }else{

      // get all methods function
      $all_methods = boolval( $assoc_args[ 'all-methods' ] );

      Cachify::flush_total_cache( $all_methods );

    }

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

    $assoc_args = wp_parse_args( $assoc_args, array( 'raw' => false ) );

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
      $cmd_optimize = function( $args, $assoc_args ) { self::optimize( $args, $assoc_args ); };

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
                ),
                array(
                    'type'     => 'assoc',
                    'name'     => 'ids',
                    'description'   => 'Flush cache for specific IDs',
                    'optional' => true,
                ),
                array(
                    'type'     => 'assoc',
                    'name'     => 'page-url',
                    'description'   => 'Flush cache for a specific IDs',
                    'optional' => true,
                ),
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
            'shortdesc' => 'Get the size of the cache',
            'synopsis'  => array(
                array(
                    'type'     => 'flag',
                    'name'     => 'raw',
                    'description'   => 'Raw size output',
                    'optional' => true,
                ),
            ),
        )
      );


    }
  }
}
