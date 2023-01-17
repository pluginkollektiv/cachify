<?php
/**
 * Cachify DB tests.
 *
 * @package Cachify
 */

/**
 * Class Test_Cachify_DB.
 *
 * Tests for the Cachify database backend.
 */
class Test_Cachify_DB extends WP_UnitTestCase {
	/**
	 * Test backend availability.
	 */
	public function test_is_available() {
		self::assertTrue( Cachify_DB::is_available() );
	}

	/**
	 * Test backend availability.
	 */
	public function test_stringify_method() {
		self::assertEquals( 'DB', Cachify_DB::stringify_method() );
	}

	/**
	 * Test the actual caching.
	 */
	public function test_caching() {
		self::assertFalse( Cachify_DB::get_item( '965b4abf2414e45036ab90c9d3f8dbc7' ) );

		Cachify_DB::store_item(
			'965b4abf2414e45036ab90c9d3f8dbc7',
			'<html><head><title>Test Me</title></head><body><p>Test Content.</p></body></html>',
			3600
		);

		$cached = Cachify_DB::get_item( '965b4abf2414e45036ab90c9d3f8dbc7' );
		self::assertIsArray( $cached, 'item was not stored' );
		self::assertEquals(
			'<html><head><title>Test Me</title></head><body><p>Test Content.</p></body></html>',
			$cached['data'],
			'unexpected data in cache'
		);
		self::assertIsInt( $cached['meta']['queries'], 'number of queries not filled' );
		self::assertIsString( $cached['meta']['timer'], 'timing not filled' );
		self::assertIsString( $cached['meta']['memory'], 'memory not filled' );
		self::assertIsInt( $cached['meta']['time'], 'time not filled' );

		// Another item.
		Cachify_DB::store_item(
			'ef7e4a0540f6cde19e6eb658c69b0064',
			'<html><head><title>Test 2</title></head><body><p>Test Content #2.</p></body></html>',
			3600
		);
		self::assertIsArray( Cachify_DB::get_item( 'ef7e4a0540f6cde19e6eb658c69b0064' ), 'second item was not stored' );

		// Delete the first item.
		Cachify_DB::delete_item( '965b4abf2414e45036ab90c9d3f8dbc7' );
		self::assertFalse( Cachify_DB::get_item( '965b4abf2414e45036ab90c9d3f8dbc7' ), 'first item was not deleted' );
		self::assertIsArray( Cachify_DB::get_item( 'ef7e4a0540f6cde19e6eb658c69b0064' ), 'second item should still be present' );
	}
}
