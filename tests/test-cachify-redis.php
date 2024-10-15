<?php
/**
 * Cachify Redis tests.
 *
 * @package Cachify
 */

/**
 * Class Test_Cachify_REDIS.
 *
 * Tests for the Cachify Redis backend.
 */
class Test_Cachify_REDIS extends WP_UnitTestCase {
	/**
	 * Test backend availability.
	 */
	public function test_is_available() {
		self::assertEquals( class_exists( 'Redis' ), Cachify_REDIS::is_available() );
	}

	/**
	 * Test backend availability.
	 */
	public function test_stringify_method() {
		self::assertEquals( 'Redis', Cachify_REDIS::stringify_method() );
	}
}
