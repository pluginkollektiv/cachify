<?php

/**
* Cachify_CLI
*/
final class Cachify_CLI {

  public static function flush_cache( $args, $assoc_args ){

    $assoc_args = wp_parse_args( $assoc_args, array( 'all-methods' => false ) );

    $all_methods = boolval( $assoc_args[ 'all-methods' ] );

    Cachify::flush_total_cache( $all_methods );

    WP_CLI::success( "Cache flushed" );

  }

  public static function add_commands() {
    if ( defined( 'WP_CLI' ) && WP_CLI ) {
      $cmd_optimize = function( $args, $assoc_args ) { self::optimize( $args, $assoc_args ); };
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
                    'optional' => true,
                ),
            ),
        )
      );
    }
  }
}
