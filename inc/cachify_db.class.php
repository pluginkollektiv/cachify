<?php


/* Quit */
defined('ABSPATH') OR exit;


/**
* Cachify_DB
*/

final class Cachify_DB {


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
		return true;
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
		return 'DB';
	}


	/**
	* Speicherung im Cache
	*
	* @since   2.0
	* @change  2.0
	*
	* @param   string   $hash      Hash des Eintrags
	* @param   string   $data      Inhalt des Eintrags
	* @param   integer  $lifetime  Lebensdauer des Eintrags
	*/

	public static function store_item($hash, $data, $lifetime)
	{
		/* Leer? */
		if ( empty($hash) or empty($data) ) {
			wp_die('DB add item: Empty input.');
		}

		/* Store */
		set_transient(
			$hash,
			array(
				'data' => $data,
				'meta' => array(
					'queries' => self::_page_queries(),
					'timer'	  => self::_page_timer(),
					'memory'  => self::_page_memory(),
					'time'	  => current_time('timestamp')
				)
			),
			$lifetime
		);
	}


	/**
	* Lesen aus dem Cache
	*
	* @since   2.0
	* @change  2.0
	*
	* @param   string  $hash  Hash des Eintrags
	* @return  mixed   $diff  Wert des Eintrags
	*/

	public static function get_item($hash)
	{
		/* Leer? */
		if ( empty($hash) ) {
			wp_die('DB get item: Empty input.');
		}

		return get_transient($hash);
	}


	/**
	* Entfernung aus dem Cache
	*
	* @since   2.0
	* @change  2.0
	*
	* @param   string  $hash  Hash des Eintrags
	* @param   string  $url   URL des Eintrags [optional]
	*/

	public static function delete_item($hash, $url = '')
	{
		/* Leer? */
		if ( empty($hash) ) {
			wp_die('DB delete item: Empty input.');
		}

		/* Löschen */
		delete_transient($hash);
	}


	/**
	* Leerung des Cache
	*
	* @since   2.0
	* @change  2.0
	*/

	public static function clear_cache()
	{
		/* Init */
		global $wpdb;

		/* Löschen */
		$wpdb->query("DELETE FROM `" .$wpdb->options. "` WHERE `option_name` LIKE ('\_transient%.cachify')");
	}


	/**
	* Ausgabe des Cache
	*
	* @since   2.0
	* @change  2.0.2
	*
	* @param   array  $cache  Array mit Cache-Werten
	*/

	public static function print_cache($cache)
	{
		/* Kein Array? */
		if ( ! is_array($cache) ) {
			return;
		}

		/* Content */
		echo $cache['data'];

		/* Signatur */
		if ( isset($cache['meta']) ) {
			echo self::_cache_signatur($cache['meta']);
		}

		/* Raus */
		exit;
	}


	/**
	* Ermittlung der Cache-Größe
	*
	* @since   2.0
	* @change  2.0
	*
	* @return  integer  $diff  Spaltengröße
	*/

	public static function get_stats()
	{
		/* Init */
		global $wpdb;

		/* Auslesen */
		return $wpdb->get_var(
			"SELECT SUM( CHAR_LENGTH(option_value) ) FROM `" .$wpdb->options. "` WHERE `option_name` LIKE ('\_transient%.cachify')"
		);
	}


	/**
	* Generierung der Signatur
	*
	* @since   2.0
	* @change  2.0.5
	*
	* @param   array   $meta  Inhalt der Metadaten
	* @return  string  $diff  Signatur als String
	*/

	private static function _cache_signatur($meta)
	{
		/* Kein Array? */
		if ( ! is_array($meta) ) {
			return;
		}

		return sprintf(
			"\n\n<!--\n%s\n%s\n%s\n%s\n-->",
			'Cachify | http://cachify.de',
			sprintf(
				'Ohne Plugin: %d DB-Anfragen, %s Sekunden, %s',
				$meta['queries'],
				$meta['timer'],
				$meta['memory']
			),
			sprintf(
				'Mit Plugin: %d DB-Anfragen, %s Sekunden, %s',
				self::_page_queries(),
				self::_page_timer(),
				self::_page_memory()
			),
			sprintf(
				'Generiert: %s zuvor',
				human_time_diff($meta['time'], current_time('timestamp'))
			)
		);
	}


	/**
	* Rückgabe der Query-Anzahl
	*
	* @since   0.1
	* @change  2.0
	*
	* @return  intval  $diff  Query-Anzahl
	*/

	private static function _page_queries()
	{
		return $GLOBALS['wpdb']->num_queries;
	}


	/**
	* Rückgabe der Ausführungszeit
	*
	* @since   0.1
	* @change  2.0
	*
	* @return  intval  $diff  Anzahl der Sekunden
	*/

	private static function _page_timer()
	{
		return timer_stop(0, 2);
	}


	/**
	* Rückgabe des Speicherverbrauchs
	*
	* @since   0.7
	* @change  2.0
	*
	* @return  string  $diff  Konvertierter Größenwert
	*/

	private static function _page_memory()
	{
		return ( function_exists('memory_get_usage') ? size_format(memory_get_usage(), 2) : 0 );
	}
}