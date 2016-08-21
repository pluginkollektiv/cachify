<?php


/* Quit */
defined('ABSPATH') OR exit;


/**
* Cachify_APC
*/

final class Cachify_APC {


	/**
	* Availability check
	*
	* @since   2.0.7
	* @change  2.0.7
	*
	* @return  boolean  true/false  TRUE when installed
	*/

	public static function is_available()
	{
		return extension_loaded('apc');
	}


	/**
	* Caching method as string
	*
	* @since   2.1.2
	* @change  2.1.2
	*
	* @return  string  Caching method
	*/

	public static function stringify‎_method() {
		return 'APC';
	}


	/**
	* Store item in cache
	*
	* @since   2.0
	* @change  2.0
	*
	* @param   string   $hash      Hash of the entry
	* @param   string   $data      Content of the entry
	* @param   integer  $lifetime  Lifetime of the entry
	*/

	public static function store_item($hash, $data, $lifetime)
	{
		/* Leer? */
		if ( empty($hash) or empty($data) ) {
			wp_die('APC add item: Empty input.');
		}

		/* Store */
		apc_store(
			$hash,
			gzencode( $data . self::_cache_signatur(), 9),
			$lifetime
		);
	}


	/**
	* Read item from cache
	*
	* @since   2.0
	* @change  2.0
	*
	* @param   string  $hash  Hash of the entry
	* @return  mixed          Content of the entry
	*/

	public static function get_item($hash)
	{
		/* Empty? */
		if ( empty($hash) ) {
			wp_die('APC get item: Empty input.');
		}

		return ( function_exists('apc_exists') ? apc_exists($hash) : apc_fetch($hash) );
	}


	/**
	* Delete item from cache
	*
	* @since   2.0
	* @change  2.0
	*
	* @param   string  $hash  Hash of the entry
	* @param   string  $url   URL of the entry [optional]
	*/

	public static function delete_item($hash, $url = '')
	{
		/* Empty? */
		if ( empty($hash) ) {
			wp_die('APC delete item: Empty input.');
		}

		/* Delete */
		apc_delete($hash);
	}


	/**
	* Clear the cache
	*
	* @since   2.0.0
	* @change  2.0.7
	*/

	public static function clear_cache()
	{
		if ( ! self::is_available() ) {
			return;
		}

		@apc_clear_cache('user');
	}


	/**
	* Print the cache
	*
	* @since   2.0
	* @change  2.0
	*/

	public static function print_cache()
	{
		return;
	}


	/**
	* Get the cache size
	*
	* @since   2.0
	* @change  2.0
	*
	* @return  mixed  Cache size
	*/

	public static function get_stats()
	{
		/* Info */
		$data = apc_cache_info('user');

		/* Empty */
		if ( empty($data['mem_size']) ) {
			return NULL;
		}

		return $data['mem_size'];
	}


	/**
	* Generate signature
	*
	* @since   2.0
	* @change  2.0.5
	*
	* @return  string  Signature string
	*/

	private static function _cache_signatur()
	{
		return sprintf(
			"\n\n<!-- %s\n%s @ %s -->",
			'Cachify | http://cachify.de',
			'APC Cache',
			date_i18n(
				'd.m.Y H:i:s',
				current_time('timestamp')
			)
		);
	}
}
