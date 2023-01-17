<?php
/**
 * Cachify APC tests.
 *
 * @package Cachify
 */

/**
 * Class Test_Cachify_APC.
 *
 * Tests for the Cachify APC backend.
 */
class Test_Cachify_APC extends WP_UnitTestCase {
	/**
	 * Test backend availability.
	 */
	public function test_is_available() {
		/*
		 * This test is a little tricky, because we need the PHP APC extension loaded.
		 * Let's just assume the equivalence between availability of the extension and the backend here.
		 */
		self::assertEquals( extension_loaded( 'apc' ), Cachify_APC::is_available() );
	}

	/**
	 * Test backend availability.
	 */
	public function test_stringify_method() {
		self::assertEquals( 'APC', Cachify_APC::stringify_method() );
	}
}
