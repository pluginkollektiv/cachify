<?php
/**
 * Cachify HDD tests.
 *
 * @package Cachify
 */

/**
 * Class Test_Cachify_HDD.
 *
 * Tests for the Cachify harddisk backend.
 */
class Test_Cachify_HDD extends WP_UnitTestCase {
	/**
	 * Test backend availability.
	 */
	public function test_is_available() {
		update_option( 'permalink_structure', '' );
		self::assertFalse( Cachify_HDD::is_available(), 'HDD backend should not be available without permalink structure' );

		update_option( 'permalink_structure', '/%postname%/' );
		self::assertTrue( Cachify_HDD::is_available(), 'HDD backend should be available without permalink structure' );
	}

	/**
	 * Test backend availability.
	 */
	public function test_stringify_method() {
		self::assertEquals( 'HDD', Cachify_HDD::stringify_method() );
	}

	/**
	 * Test GZip availability.
	 */
	public function test_is_gzip_enabled() {
		self::assertTrue( Cachify_HDD::is_gzip_enabled(), 'GZip should be enabled by default' );

		$capture = null;
		add_filter(
			'cachify_create_gzip_files',
			function ( $original ) use ( &$capture ) {
				$capture = $original;

				return false;
			}
		);
		self::assertFalse( Cachify_HDD::is_gzip_enabled(), 'GZip should be disabled by filter' );
		self::assertTrue( $capture, 'Filter was not applied' );
	}

	/**
	 * Test the actual caching.
	 */
	public function test_caching() {
		self::assertFalse( Cachify_HDD::get_item( '965b4abf2414e45036ab90c9d3f8dbc7' ) );

		self::go_to( '/testme/' );
		Cachify_HDD::store_item(
			'965b4abf2414e45036ab90c9d3f8dbc7', // Ignored.
			'<html><head><title>Test Me</title></head><body><p>Test Content.</p></body></html>',
			3600, // Ignored.
			false
		);
		self::assertTrue( Cachify_HDD::get_item( '965b4abf2414e45036ab90c9d3f8dbc7' ) );
		self::assertTrue( is_file( CACHIFY_CACHE_DIR . DIRECTORY_SEPARATOR . 'example.org/testme/index.html' ) );
		$cached = file_get_contents( CACHIFY_CACHE_DIR . DIRECTORY_SEPARATOR . 'example.org/testme/index.html' );
		self::assertStringStartsWith(
			'<html><head><title>Test Me</title></head><body><p>Test Content.</p></body></html>

<!-- Cachify | https://cachify.pluginkollektiv.org
Generated @ ',
			$cached
		);
		self::assertStringEndsWith( ' -->', $cached );

		// A subpage
		self::go_to( '/testme/sub' );
		Cachify_HDD::store_item(
			'965b4abf2414e45036ab90c9d3f8dbc7', // Ignored.
			'<html><head><title>Test Me</title></head><body><p>This is a subpage.</p></body></html>',
			3600, // Ignored.
			false
		);
		self::assertTrue( Cachify_HDD::get_item( '965b4abf2414e45036ab90c9d3f8dbc7' ) );
		self::assertTrue( is_file( CACHIFY_CACHE_DIR . DIRECTORY_SEPARATOR . 'example.org/testme/sub/index.html' ) );

		// Another item.
		self::go_to( '/test2/' );
		Cachify_HDD::store_item(
			'ef7e4a0540f6cde19e6eb658c69b0064', // Ignored.
			'<html><head><title>Test 2</title></head><body><p>Test Content #2.</p></body></html>',
			3600, // Ignored.
			true
		);
		self::assertTrue( is_file( CACHIFY_CACHE_DIR . DIRECTORY_SEPARATOR . 'example.org/test2/index.html' ) );
		$cached = file_get_contents( CACHIFY_CACHE_DIR . DIRECTORY_SEPARATOR . 'example.org/test2/index.html' );
		self::assertStringStartsWith(
			'<html><head><title>Test 2</title></head><body><p>Test Content #2.</p></body></html>

<!-- Cachify | https://cachify.pluginkollektiv.org
HDD Cache @ ',
			$cached
		);
		self::assertStringEndsWith( ' -->', $cached );

		// Delete the first item.
		Cachify_HDD::delete_item( '965b4abf2414e45036ab90c9d3f8dbc7', 'http://example.org/testme/' );
		self::assertFalse( is_file( CACHIFY_CACHE_DIR . DIRECTORY_SEPARATOR . 'example.org/testme/index.html' ), 'first item was not deleted' );
		self::assertTrue( is_file( CACHIFY_CACHE_DIR . DIRECTORY_SEPARATOR . 'example.org/test2/index.html' ), 'second item should still be present' );
		self::assertTrue( is_file( CACHIFY_CACHE_DIR . DIRECTORY_SEPARATOR . 'example.org/testme/sub/index.html' ), 'subpage should now have been deleted' );

		// Delete the subpage.
		Cachify_HDD::delete_item( '965b4abf2414e45036ab90c9d3f8dbc7', 'http://example.org/testme/sub' );
		self::assertFalse( is_dir( CACHIFY_CACHE_DIR . DIRECTORY_SEPARATOR . 'example.org/testme/sub/index.html' ), 'subpage item was not deleted' );
		self::assertFalse( is_dir( CACHIFY_CACHE_DIR . DIRECTORY_SEPARATOR . 'example.org/testme/sub' ), 'empty directory was not deleted' );

		// Clear the cache.
		Cachify_HDD::clear_cache();
		self::assertFalse( is_dir( CACHIFY_CACHE_DIR . DIRECTORY_SEPARATOR . 'example.org/testme' ), 'empty directory was not deleted' );
		self::assertFalse( is_dir( CACHIFY_CACHE_DIR . DIRECTORY_SEPARATOR . 'example.org/test2' ), 'second test page was not deleted' );
		self::assertFalse( Cachify_HDD::get_item( '965b4abf2414e45036ab90c9d3f8dbc7' ) );
	}
}
