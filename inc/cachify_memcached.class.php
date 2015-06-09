<?php


/* Quit */
defined('ABSPATH') OR exit;


/**
* Cachify_MEMCACHED
*/

final class Cachify_MEMCACHED {


	/**
	* Memcached-Object
	*
	* @since  2.0.7
	* @var    object
	*/

	private static $_memcached;


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
		return class_exists('Memcached') && isset($_SERVER['SERVER_SOFTWARE']) && strpos(strtolower($_SERVER['SERVER_SOFTWARE']), 'nginx') !== false;
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
		return 'Memcached';
	}


	/**
	* Speicherung im Cache
	*
	* @since   2.0.7
	* @change  2.0.7
	*
	* @param   string   $hash      Hash des Eintrags
	* @param   string   $data      Inhalt des Eintrags
	* @param   integer  $lifetime  Lebensdauer des Eintrags
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
		self::$_memcached->set(
			self::_file_path(),
			$data . self::_cache_signatur(),
			$lifetime
		);
	}


	/**
	* Lesen aus dem Cache
	*
	* @since   2.0.7
	* @change  2.0.7
	*
	* @param   string  $hash  Hash des Eintrags
	* @return  mixed   $diff  Wert des Eintrags
	*/

	public static function get_item($hash)
	{
		/* Server connect */
		if ( ! self::_connect_server() ) {
			return;
		}

		/* Get item */
		return self::$_memcached->get(
			self::_file_path()
		);
	}


	/**
	* Entfernung aus dem Cache
	*
	* @since   2.0.7
	* @change  2.0.7
	*
	* @param   string  $hash  Hash des Eintrags
	* @param   string  $url   URL des Eintrags [optional]
	*/

	public static function delete_item($hash, $url = '')
	{
		/* Server connect */
		if ( ! self::_connect_server() ) {
			return;
		}

		/* Delete */
		self::$_memcached->delete(
			self::_file_path($url)
		);
	}


	/**
	* Leerung des Cache
	*
	* @since   2.0.7
	* @change  2.0.7
	*/

	public static function clear_cache()
	{
		/* Server connect */
		if ( ! self::_connect_server() ) {
			return;
		}

		/* Flush */
		@self::$_memcached->flush();
	}


	/**
	* Ausgabe des Cache
	*
	* @since   2.0.7
	* @change  2.0.7
	*/

	public static function print_cache()
	{
		return;
	}


	/**
	* Ermittlung der Cache-Größe
	*
	* @since   2.0.7
	* @change  2.0.7
	*
	* @return  mixed  $diff  Cache-Größe
	*/

	public static function get_stats()
	{
		/* Server connect */
		if ( ! self::_connect_server() ) {
			wp_die('MEMCACHE: Not enabled.');
		}

		/* Infos */
		$data = self::$_memcached->getStats();

		/* No stats? */
		if ( empty($data) ) {
			return NULL;
		}

		/* Get first key */
		$data = $data[key($data)];

		/* Leer */
		if ( empty($data['bytes']) ) {
			return NULL;
		}

		return $data['bytes'];
	}


	/**
	* Generierung der Signatur
	*
	* @since   2.0.7
	* @change  2.0.7
	*
	* @return  string  $diff  Signatur als String
	*/

	private static function _cache_signatur()
	{
		return sprintf(
			"\n\n<!-- %s\n%s @ %s -->",
			'Cachify | http://cachify.de',
			'Memcached',
			date_i18n(
				'd.m.Y H:i:s',
				current_time('timestamp')
			)
		);
	}


	/**
	* Pfad der Cache-Datei
	*
	* @since   2.0.7
	* @change  2.0.7
	*
	* @param   string  $path  Request-URI oder Permalink [optional]
	* @return  string  $diff  Pfad zur Cache-Datei
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
	* Connect to Memcached server
	*
	* @since   2.0.7
	* @change  2.1.8
	*
	* @hook    array  cachify_memcached_servers  Array with memcached servers
	*
	* @return  boolean  true/false  TRUE bei Erfolg
	*/

	private static function _connect_server()
	{
		/* Not enabled? */
		if ( ! self::is_available() ) {
			return false;
		}

		/* Already connected */
		if ( is_object(self::$_memcached) ) {
			return true;
		}

		/* Init */
		self::$_memcached = new Memcached();

		/* Set options */
		if ( defined('HHVM_VERSION') ) {
			self::$_memcached->setOption( Memcached::OPT_COMPRESSION, false );
			self::$_memcached->setOption( Memcached::OPT_BUFFER_WRITES, true );
			self::$_memcached->setOption( Memcached::OPT_BINARY_PROTOCOL, true );
		} else {
			self::$_memcached->setOptions(
				array(
					Memcached::OPT_COMPRESSION => false,
					Memcached::OPT_BUFFER_WRITES => true,
					Memcached::OPT_BINARY_PROTOCOL => true
				)
			);
		}

		/* Connect */
		self::$_memcached->addServers(
			(array)apply_filters(
				'cachify_memcached_servers',
				array(
					array(
						'127.0.0.1',
						11211
					)
				)
			)
		);

		return true;
	}
}