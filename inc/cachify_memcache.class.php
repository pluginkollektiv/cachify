<?php


/* Quit */
defined('ABSPATH') OR exit;

/**
* Cachify_MEMCACHED
*/

final class Cachify_MEMCACHE {


	/**
	* Memcache-Object
	*
	* @since  2.3.0
	* @var    object
	*/

	private static $_memcache;
  
	/**
	* Keys stats
	*
	* @since  2.3.0
	* @var    array
	*/
  
	private static $stats_keys = NULL;


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
		return class_exists('Memcache');
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
		return 'Memcache';
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
			wp_die('MEMCACHE add item: Empty input.');
		}

		/* Server connect */
		if ( ! self::_connect_server() ) {
			return;
		}

		/* Add item */
		self::$_memcache->set(
			self::_file_path(),
			$data . self::_cache_signatur(),
			0 , //no compression
			$lifetime
		);
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
		return self::$_memcache->get(
			self::_file_path()
		);
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
		self::$_memcache->delete(
			self::_file_path($url)
		);
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
	//	@self::$_memcache->flush();
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
			wp_die('MEMCACHE: Not enabled.');
		}

		/* Info */
		if ( empty(self::$stats_keys) ) {
			self::_getMemcacheSiteKeys();
		}
		$bytes=0;
		foreach (self::$stats_keys as $key => $size) {
			$bytes+=$size;
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
			wp_die('MEMCACHE: Not enabled.');
		}

		/* Info */
		if ( empty(self::$stats_keys) ) {
			self::_getMemcacheSiteKeys();
		}

		/* No stats? */
		if ( empty(self::$stats_keys) ) {
			return NULL;
		}

		return count(self::$stats_keys);
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
			'Memcache',
			date_i18n(
				'd.m.Y H:i:s',
				current_time('timestamp')
			)
		);
	}


	/**
	* Path of cache file
	*
	* @since   2.3.0
	* @change  2.3.0
	*
	* @param   string  $path  Request-URI or Permalink [optional]
	* @return  string         Path to cache file
	*/

	private static function _file_path($path = NULL)
	{
		return trailingslashit(
			sprintf(
				'%s%s',
				$_SERVER['HTTP_HOST'],
				parse_url(
					( $path ? $path : $_SERVER['REQUEST_URI'] ),
					PHP_URL_PATH
				)
			)
		);
	}


	/**
	* Connect to Memcache server
	*
	* @since   2.3.0
	* @change  2.3.0
	*
	* @hook    array  cachify_memcache_servers  Array with memcache servers
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
		if ( is_object(self::$_memcache) ) {
			return true;
		}

		/* Init */
		self::$_memcache = new Memcache();

		/* Connect */
		self::$_memcache->connect('127.0.0.1',11211);

		return true;
	}
  
	/**
	* Retrive all site keys and sizes
	*
	* @since   2.3.0
	* @change  2.3.0
	*
	* @ param int $limit
	*/
  
	private function _getMemcacheSiteKeys ($limit = 10000)
	{
		$keysFound = array();
      
		$find=self::_file_path(get_home_url());
  
		$slabs = self::$_memcache->getExtendedStats('slabs');
		foreach ($slabs as $serverSlabs) {
			foreach ($serverSlabs as $slabId => $slabMeta) {
				try {
					$cacheDump = self::$_memcache->getExtendedStats('cachedump', (int) $slabId, 1000);
				} catch (Exception $e) {
					continue;
				}
  
				if (!is_array($cacheDump)) {
					continue;
				}
              
				foreach ($cacheDump as $dump) {
  
					if (!is_array($dump)) {
						continue;
					}
					foreach ($dump as $key => $value) {
						$pos = strpos($key, $find);
						if ($pos !== false && $pos===0) {
							$val=self::$_memcache->get($key);
							$keysFound[$key] = strlen($val);
							if (count($keysFound) == $limit) {
								self::$stats_keys = $keysFound; 
							}
						}
					}
				}
			}
		}
		self::$stats_keys = $keysFound;
	}
}
