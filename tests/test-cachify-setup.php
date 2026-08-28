<?php
/**
 * Cachify setup tests.
 *
 * @package Cachify
 */

/**
 * Class Test_Cachify_Setup.
 *
 * Tests for generated server configuration.
 */
class Test_Cachify_Setup extends WP_UnitTestCase {
	/**
	 * Test generated Apache configuration.
	 */
	public function test_htaccess_uses_dynamic_values() {
		ob_start();
		include dirname( __DIR__ ) . '/inc/setup/cachify.hdd.htaccess.php';
		$output = ob_get_clean();

		$content_path = untrailingslashit( '/' . ltrim( (string) wp_parse_url( content_url(), PHP_URL_PATH ), '/' ) );

		self::assertStringContainsString( Cachify::get_bypass_cookie_pattern(), $output );
		self::assertStringContainsString( $content_path . '/cache/cachify', $output );
		self::assertStringNotContainsString( '(wp-postpass|wordpress_logged_in|comment_author)_', $output );
	}

	/**
	 * Test generated HDD nginx configuration.
	 */
	public function test_hdd_nginx_uses_dynamic_values() {
		ob_start();
		include dirname( __DIR__ ) . '/inc/setup/cachify.hdd.nginx.php';
		$output = ob_get_clean();

		$content_path = untrailingslashit( '/' . ltrim( (string) wp_parse_url( content_url(), PHP_URL_PATH ), '/' ) );

		self::assertStringContainsString( Cachify::get_bypass_cookie_pattern(), $output );
		self::assertStringContainsString( $content_path . '/cache/cachify', $output );
		self::assertStringNotContainsString( '(wp-postpass|wordpress_logged_in|comment_author)_', $output );
	}

	/**
	 * Test generated Memcached nginx configuration.
	 */
	public function test_memcached_nginx_uses_dynamic_cookie_names() {
		ob_start();
		include dirname( __DIR__ ) . '/inc/setup/cachify.memcached.nginx.php';
		$output = ob_get_clean();

		self::assertStringContainsString( Cachify::get_bypass_cookie_pattern(), $output );
		self::assertStringNotContainsString( '(wp-postpass|wordpress_logged_in|comment_author)_', $output );
	}
}
