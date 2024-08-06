<?php
/**
 * Cachify NOOP tests.
 *
 * @package Cachify
 */

/**
 * Class Test_Cachify_NOOP.
 */
class Test_Cachify_NOOP extends WP_UnitTestCase {
	/**
	 * Test backend availability.
	 */
	public function test_is_available() {
		self::assertTrue( Cachify_NOOP::is_available(), 'NOOP backend should always be available' );
	}

	/**
	 * Test backend availability.
	 */
	public function test_stringify_method() {
		self::assertSame( 'NOOP', Cachify_NOOP::stringify_method() );
	}

	/**
	 * Test unavailable method.
	 */
	public function test_unavailable_method() {
		$noop = new Cachify_NOOP( 'testme' );
		self::assertSame( 'testme', $noop->unavailable_method, 'unexpected name of unavailable method' );
		$noop = new Cachify_NOOP();
		self::assertSame( '', $noop->unavailable_method, 'unexpected default name of unavailable method' );
	}

	/**
	 * Test the actual caching.
	 */
	public function test_caching() {
		self::go_to( '/testme/' );
		Cachify_NOOP::store_item(
			'965b4abf2414e45036ab90c9d3f8dbc7',
			'<html><head><title>Test Me</title></head><body><p>Test Content.</p></body></html>',
			3600,
			false
		);
		self::assertFalse(
			Cachify_NOOP::get_item('965b4abf2414e45036ab90c9d3f8dbc7'),
			"item should not have been stored"
		);

		Cachify_NOOP::delete_item( '965b4abf2414e45036ab90c9d3f8dbc7' );
		self::assertFalse(
			Cachify_NOOP::get_item('965b4abf2414e45036ab90c9d3f8dbc7'),
			"item present after deletion"
		);
	}
}
