<?php
/**
 * Cachify tests.
 *
 * @package Cachify
 */

/**
 * Class Test_Cachify.
 *
 * Tests for generic Cachify routines.
 */
class Test_Cachify extends WP_UnitTestCase {
	/**
	 * Test registration of flush hooks.
	 */
	public function test_register_flush_cache_hooks() {
		$original_capture = null;

		// Add a filter hook.
		add_filter(
			'cachify_flush_cache_hooks',
			function ( $original ) use ( &$original_capture ) {
				$original_capture = $original;

				return array(
					'test_1' => 10,
					'test_2' => 20,
				);
			}
		);

		// Call flush registration.
		Cachify::register_flush_cache_hooks();

		// Verify that the filter has been called.
		self::assertNotNull( $original_capture, 'Filter not called' );
		self::assertEquals( 12, count( $original_capture ), 'Unexpected number of default hooks' );
		self::assertEmpty(
			array_filter(
				$original_capture,
				function( $v ) {
					return 10 !== $v;
				}
			),
			'All default filters should have priority 10'
		);

		// Verify that the action has been hooked with given priority.
		self::assertEquals(
			10,
			has_action( 'test_1', array( Cachify::class, 'flush_total_cache' ) ),
			'Flush action not hooked as expected'
		);
		self::assertEquals(
			20,
			has_action( 'test_2', array( Cachify::class, 'flush_total_cache' ) ),
			'Flush action not hooked as expected'
		);
	}

	/**
	 * Test registration of scripts.
	 */
	public function test_register_scripts() {
		Cachify::register_scripts();
		self::assertTrue( wp_script_is( 'cachify-admin-bar-flush', 'registered' ) );
		$script = wp_scripts()->registered['cachify-admin-bar-flush'];
		self::assertStringEndsWith(
			'/js/admin-bar-flush.min.js',
			$script->src,
			'unexpected script source'
		);
	}

	/**
	 * Test registration of styles.
	 */
	public function test_register_styles() {
		Cachify::register_styles();
		self::assertTrue( wp_style_is( 'cachify-dashboard', 'registered' ) );
		self::assertTrue( wp_style_is( 'cachify-admin-bar-flush', 'registered' ) );

		$style = wp_styles()->registered['cachify-dashboard'];
		self::assertStringEndsWith(
			'/css/dashboard.min.css',
			$style->src,
			'unexpected dashboard style source'
		);

		$style = wp_styles()->registered['cachify-admin-bar-flush'];
		self::assertStringEndsWith(
			'/css/admin-bar-flush.min.css',
			$style->src,
			'unexpected admin bar style source'
		);
	}

	/**
	 * Test single site plugin activation.
	 */
	public function test_on_activation() {
		self::assertFalse( get_option( 'cachify' ), 'Cachify option should not be initialized initially' );
		Cachify::on_activation();
		self::assertEquals( array() , get_option( 'cachify' ), 'Cachify option not initialized' );
	}


	/**
	 * Test hook for robots.txt customization.
	 */
	public function test_robots_txt() {
		// Initial robots.txt content.
		$robots_txt = "User-agent: *\nDisallow: /wordpress/wp-admin/\nAllow: /wordpress/wp-admin/admin-ajax.php\n";

		// DB cache enabled.
		update_option(
			'cachify',
			array(
				'use_apc'           => Cachify::METHOD_DB,
				'change_robots_txt' => 1,
			)
		);
		new Cachify();

		self::assertEquals(
			$robots_txt,
			Cachify::robots_txt( $robots_txt ),
			'robots.txt should not be modified using DB cache'
		);

		// HDD cache enabled.
		update_option(
			'cachify',
			array(
				'use_apc'           => Cachify::METHOD_HDD,
				'change_robots_txt' => 1,
			)
		);
		new Cachify();

		self::assertEquals(
			$robots_txt . "\nUser-agent: *\nDisallow: */cache/cachify/\n",
			Cachify::robots_txt( $robots_txt ),
			'robots.txt should have been modified using HDD cache'
		);

		// Disable robots.txt modification.
		update_option(
			'cachify',
			array(
				'use_apc'           => Cachify::METHOD_HDD,
				'change_robots_txt' => 0,
			)
		);

		self::assertEquals(
			$robots_txt . "\nUser-agent: *\nDisallow: */cache/cachify/\n",
			Cachify::robots_txt( $robots_txt ),
			'robots.txt should have been modified using HDD cache'
		);
	}
}
