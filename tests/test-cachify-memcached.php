<?php
/**
 * Cachify Memcached tests.
 *
 * @package Cachify
 */

/**
 * Class Test_Cachify_Memcached.
 *
 * Tests for the Cachify Memcached backend.
 */
class Test_Cachify_MEMCACHED extends WP_UnitTestCase {
	/**
	 * Test backend availability.
	 */
	public function test_is_available() {
		/*
		 * This test is a little tricky, because we need the PHP memcached extension loaded and only if we
		 * are on a nginx webserver.
		 * Let's just assume the equivalence between availability of the extension and the backend here.
		 */
		global $_SERVER;
		$_SERVER['SERVER_SOFTWARE'] = 'nginx';
		self::assertEquals( class_exists( 'Memcached' ), Cachify_MEMCACHED::is_available() );
	}

	/**
	 * Test backend availability.
	 */
	public function test_stringify_method() {
		self::assertEquals( 'Memcached', Cachify_MEMCACHED::stringify_method() );
	}
}
