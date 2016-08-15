<?php
/*
Plugin Name: Cachify
Description: Easy to use WordPress caching plugin. Serving static blog pages from database, disk, Memcached or APC.
Author:      pluginkollektiv
Author URI:  http://pluginkollektiv.org
Plugin URI:  https://wordpress.org/plugins/cachify/
License:     GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Version:     2.2.4
Text Domain: cachify
Domain Path: /lang
*/

/*
Copyright (C)  2011-2015 Sergej Müller

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License along
with this program; if not, write to the Free Software Foundation, Inc.,
51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
*/


/* Quit */
defined('ABSPATH') OR exit;


/* Konstanten */
define('CACHIFY_FILE', __FILE__);
define('CACHIFY_DIR', dirname(__FILE__));
define('CACHIFY_BASE', plugin_basename(__FILE__));
define('CACHIFY_CACHE_DIR', WP_CONTENT_DIR. '/cache/cachify');


/* Hooks */
add_action(
	'plugins_loaded',
	array(
		'Cachify',
		'instance'
	)
);
register_activation_hook(
	__FILE__,
	array(
		'Cachify',
		'on_activation'
	)
);
register_deactivation_hook(
	__FILE__,
	array(
		'Cachify',
		'on_deactivation'
	)
);
register_uninstall_hook(
	__FILE__,
	array(
		'Cachify',
		'on_uninstall'
	)
);


/* Autoload Init */
spl_autoload_register('cachify_autoload');

/* Autoload Funktion */
function cachify_autoload($class) {
	if ( in_array($class, array('Cachify', 'Cachify_APC', 'Cachify_DB', 'Cachify_HDD', 'Cachify_MEMCACHED')) ) {
		require_once(
			sprintf(
				'%s/inc/%s.class.php',
				CACHIFY_DIR,
				strtolower($class)
			)
		);
	}
}
