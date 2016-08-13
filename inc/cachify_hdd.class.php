<?php


/* Quit */
defined('ABSPATH') OR exit;


/**
* Cachify_HDD
*/

final class Cachify_HDD {


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
		return get_option('permalink_structure');
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
		return 'HDD';
	}


	/**
	* Speicherung im Cache
	*
	* @since   2.0
	* @change  2.0
	*
	* @param   string   $hash      Hash des Eintrags [optional]
	* @param   string   $data      Inhalt des Eintrags
	* @param   integer  $lifetime  Lebensdauer des Eintrags [optional]
	*/

	public static function store_item($hash, $data, $lifetime)
	{
		/* Leer? */
		if ( empty($data) ) {
			wp_die('HDD add item: Empty input.');
		}

		/* Speichern */
		self::_create_files(
			$data . self::_cache_signatur()
		);
	}


	/**
	* Lesen aus dem Cache
	*
	* @since   2.0
	* @change  2.0
	*
	* @return  boolean  $diff  TRUE wenn Cache vorhanden
	*/

	public static function get_item()
	{
		return is_readable(
			self::_file_html()
		);
	}


	/**
	* Entfernen aus dem Cache
	*
	* @since   2.0
	* @change  2.0
	*
	* @param   string   $hash  Hash des Eintrags [optional]
	* @param   string   $url   URL des Eintrags
	*/

	public static function delete_item($hash = '', $url)
	{
		/* Leer? */
		if ( empty($url) ) {
			wp_die('HDD delete item: Empty input.');
		}

		/* Löschen */
		self::_clear_dir(
			self::_file_path($url)
		);
	}


	/**
	* Leerung des Cache
	*
	* @since   2.0
	* @change  2.0
	*/

	public static function clear_cache()
	{
		self::_clear_dir(
			CACHIFY_CACHE_DIR
		);
	}


	/**
	* Ausgabe des Cache
	*
	* @since   2.0
	* @change  2.0
	*/

	public static function print_cache()
	{
		return;
	}


	/**
	* Ermittlung der Cache-Größe
	*
	* @since   2.0
	* @change  2.0
	*
	* @return  integer  $diff  Ordnergröße
	*/

	public static function get_stats()
	{
		return self::_dir_size( CACHIFY_CACHE_DIR );
	}


	/**
	* Generierung der Signatur
	*
	* @since   2.0
	* @change  2.0.5
	*
	* @return  string  $diff  Signatur als String
	*/

	private static function _cache_signatur()
	{
		return sprintf(
			"\n\n<!-- %s\n%s @ %s -->",
			'Cachify | http://cachify.de',
			'HDD Cache',
			date_i18n(
				'd.m.Y H:i:s',
				current_time('timestamp')
			)
		);
	}


	/**
	* Initialisierung des Cache-Speichervorgangs
	*
	* @since   2.0
	* @change  2.0
	*
	* @param   string  $data  Cache-Inhalt
	*/

	private static function _create_files($data)
	{
		/* Ordner anlegen */
		if ( ! wp_mkdir_p( self::_file_path() ) ) {
			wp_die('Unable to create directory.');
		}

		/* Dateien schreiben */
		self::_create_file( self::_file_html(), $data );
		self::_create_file( self::_file_gzip(), gzencode($data, 9) );
	}


	/**
	* Anlegen der Cache-Datei
	*
	* @since   2.0
	* @change  2.0
	*
	* @param   string  $file  Pfad der Cache-Datei
	* @param   string  $data  Cache-Inhalt
	*/

	private static function _create_file($file, $data)
	{
		/* Beschreibbar? */
		if ( ! $handle = @fopen($file, 'wb') ) {
			wp_die('Could not write file.');
		}

		/* Schreiben */
		@fwrite($handle, $data);
		fclose($handle);
		clearstatcache();

		/* Permissions */
		$stat = @stat( dirname($file) );
		$perms = $stat['mode'] & 0007777;
		$perms = $perms & 0000666;
		@chmod($file, $perms);
		clearstatcache();
	}


	/**
	* Rekrusive Leerung eines Ordners
	*
	* @since   2.0
	* @change  2.0.5
	*
	* @param   string  $dir  Ordnerpfad
	*/

	private static function _clear_dir($dir) {
		/* Weg mit dem Slash */
		$dir = untrailingslashit($dir);

		/* Ordner? */
		if ( ! is_dir($dir) ) {
			return;
		}

		/* Einlesen */
		$objects = array_diff(
			scandir($dir),
			array('..', '.')
		);

		/* Leer? */
		if ( empty($objects) ) {
			return;
		}

		/* Loopen */
		foreach ( $objects as $object ) {
			/* Um Pfad erweitern */
			$object = $dir. DIRECTORY_SEPARATOR .$object;

			/* Ordner/Datei */
			if ( is_dir($object) ) {
				self::_clear_dir($object);
			} else {
				unlink($object);
			}
		}

		/* Killen */
		@rmdir($dir);

		/* Aufräumen */
		clearstatcache();
	}


	/**
	* Ermittlung der Ordnergröße
	*
	* @since   2.0
	* @change  2.0
	*
	* @param   string  $dir   Ordnerpfad
	* @return  mixed   $size  Ordnergröße
	*/

	public static function _dir_size($dir = '.')
	{
		/* Ordner? */
		if ( ! is_dir($dir) ) {
			return;
		}

		/* Einlesen */
		$objects = array_diff(
			scandir($dir),
			array('..', '.')
		);

		/* Leer? */
		if ( empty($objects) ) {
			return;
		}

		/* Init */
		$size = 0;

		/* Loopen */
		foreach ( $objects as $object ) {
			/* Um Pfad erweitern */
			$object = $dir. DIRECTORY_SEPARATOR .$object;

			/* Ordner/Datei */
			if ( is_dir($object) ) {
				$size += self::_dir_size($object);
			} else {
				$size += filesize($object);
			}
		}

		return $size;
	}


	/**
	* Pfad der Cache-Datei
	*
	* @since   2.0
	* @change  2.0
	*
	* @param   string  $path  Request-URI oder Permalink [optional]
	* @return  string  $diff  Pfad zur Cache-Datei
	*/

	private static function _file_path($path = NULL)
	{
		$prefix = is_ssl() ? 'https-' : '';

		$path = sprintf(
			'%s%s%s%s%s',
			CACHIFY_CACHE_DIR,
			DIRECTORY_SEPARATOR,
			$prefix,
			parse_url(
				'http://' .strtolower($_SERVER['HTTP_HOST']),
				PHP_URL_HOST
			),
			parse_url(
				( $path ? $path : $_SERVER['REQUEST_URI'] ),
				PHP_URL_PATH
			)
		);

		if ( validate_file($path) > 0 ) {
			wp_die('Invalide file path.');
		}

		return trailingslashit($path);
	}


	/**
	* Pfad der HTML-Datei
	*
	* @since   2.0
	* @change  2.0
	*
	* @return  string  $diff  Pfad zur HTML-Datei
	*/

	private static function _file_html()
	{
		return self::_file_path(). 'index.html';
	}


	/**
	* Pfad der GZIP-Datei
	*
	* @since   2.0
	* @change  2.0
	*
	* @return  string  $diff  Pfad zur GZIP-Datei
	*/

	private static function _file_gzip()
	{
		return self::_file_path(). 'index.html.gz';
	}
}