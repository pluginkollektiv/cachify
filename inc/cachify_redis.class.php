<?php


/* Quit */
defined('ABSPATH') OR exit;

/**
* Cachify_REDIS
*/

final class Cachify_REDIS {


	/**
	* Redis-Object
	*
	* @since  2.3.0
	* @var    object
	*/

	private static $_redis;


	/**
	* Availability check
	*
	* @since   2.3.0
	* @change  2.3.0
	*
	* @return  boolean  true/false  TRUE when installed
	*/

	public static function is_available()
	{
		return class_exists('Redis');
	}


	/**
	* Caching method as string
	*
	* @since   2.3.0
	* @change  2.3.0
	*
	* @return  string  Caching method
	*/

	public static function stringify_method() {
		return 'Redis';
	}


	/**
	* Store item in cache
	*
	* @since   2.3.0
	* @change  2.3.0
	*
	* @param   string   $hash      Hash of the entry
	* @param   string   $data      Content of the entry
	* @param   integer  $lifetime  Lifetime of the entry
	*/

	public static function store_item($hash, $data, $lifetime)
	{
		/* Empty? */
		if ( empty($data) ) {
			wp_die('REDIS add item: Empty input.');
		}

		/* Server connect */
		if ( ! self::_connect_server() ) {
			return;
		}
    
		$data = serialize( $data . self::_cache_signatur() );

		/* Add item */
		$result = self::$_redis->setEx( $hash, $lifetime, $data );

	}


	/**
	* Read item from cache
	*
	* @since   2.3.0
	* @change  2.3.0
	*
	* @param   string  $hash  Hash of the entry
	* @return  mixed          Content of the entry
	*/

	public static function get_item($hash)
	{
		/* Server connect */
		if ( ! self::_connect_server() ) {
			return;
		}

		/* Get item */
		$data=self::$_redis->get($hash);
     
		$data = unserialize( $data );
    
		return $data;
	}


	/**
	* Delete item from cache
	*
	* @since   2.3.0
	* @change  2.3.0
	*
	* @param   string  $hash  Hash of the entry
	* @param   string  $url   URL of the entry [optional]
	*/

	public static function delete_item($hash, $url = '')
	{
		/* Server connect */
		if ( ! self::_connect_server() ) {
			return;
		}

		/* Delete */
    		self::$_redis->delete( $hash );
	}


	/**
	* Clear the cache
	*
	* @since   2.3.0
	* @change  2.3.0
	*/
	//TODO
	public static function clear_cache()
	{
		/* Server connect */
		if ( ! self::_connect_server() ) {
			return;
		}

		/* Flush */
	//	@self::$_redis->flush();
	}


	/**
	* Print the cache
	*
	* @since   2.3.0
	* @change  2.3.0
	*/

	public static function print_cache($cache)
	{
    		/* Output the cache */
		echo $cache;
		
		/* Quit */
		exit;
	}


	/**
	* Get the cache size
	*
	* @since   2.3.0
	* @change  2.3.0
	*
	* @return  integer  Cache size
	*/

	public static function get_stats()
	{
		/* Server connect */
		if ( ! self::_connect_server() ) {
			wp_die('REDIS: Not enabled.');
		}

		/* Info */
		$it = 'cachify:'.$_SERVER['HTTP_HOST'] . ':*'; 
		$arr_keys = self::$_redis->keys($it);
		$bytes=0;
		foreach($arr_keys as $str_key) {
			$ksize=self::$_redis->strlen($str_key);
			$bytes+=$ksize;
		}

		return $bytes;
	}
  
  	/**
	* Get the number of cached pages
	*
	* @since   2.3.0
	* @change  2.3.0
	*
	* @return  integer  Cached pages
	*/

	public static function get_pages()
	{
		/* Server connect */
		if ( ! self::_connect_server() ) {
			wp_die('REDIS: Not enabled.');
		}

		/* Info */
		$it = 'cachify:'.$_SERVER['HTTP_HOST'] . ':*'; 
		$arr_keys = self::$_redis->keys($it);

		return count($arr_keys);   
	}


	/**
	* Generate signature
	*
	* @since   2.3.0
	* @change  2.3.0
	*
	* @return  string  Signature string
	*/

	private static function _cache_signatur()
	{
		return sprintf(
			"\n\n<!-- %s\n%s @ %s -->",
			'Cachify | http://cachify.de',
			'Redis',
			date_i18n(
				'd.m.Y H:i:s',
				current_time('timestamp')
			)
		);
	}

	/**
	* Connect to Redis server
	*
	* @since   2.3.0
	* @change  2.3.0
	*
	* @hook    array  cachify_redis_servers  Array with redis servers
	*
	* @return  boolean  true/false  TRUE on success
	*/

	private static function _connect_server()
	{
		/* Not enabled? */
		if ( ! self::is_available() ) {
			return false;
		}

		/* Already connected */
		if ( is_object(self::$_redis) ) {
			return true;
		}

		/* Init */
		self::$_redis = new Redis();

		/* Connect */
		self::$_redis->connect('127.0.0.1', 6379 );
		if(!self::$_redis->isConnected()) return false;
		return true;
	}
}
