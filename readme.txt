# Cachify #
* Contributors:      pluginkollektiv
* Donate link:       https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=TD4AMD2D8EMZW
* Tags:              acceleration, apc, cache, caching, compress, database cache, db-cache, disk cache, disk caching, google, hdd, html compression, memcached, minify, minimize, optimize, page cache, performance, quick cache, speed
* Requires at least: 4.4
* Tested up to:      5.4
* Requires PHP:      5.2.4
* Stable tag:        2.3.0
* License:           GPLv2 or later
* License URI:       http://www.gnu.org/licenses/gpl-2.0.html

Smart, efficient cache solution for WordPress. Use DB, HDD, APC or Memcached for storing your blog pages. Make WordPress faster!

## Description ##
*Cachify* optimizes your page loads by caching posts, pages and custom post types as static content. You can choose between caching via database, on the web server’s hard drive (HDD), Memcached (only on Nginx) or — thanks to APC (Alternative PHP Cache) — directly in the web server’s system cache. Whenever a page or post is loaded, it can be pulled directly from the cache. The amount of database queries and PHP requests will dramatically decrease towards zero, depending on the caching method you chose.

### Features ###
* Works with custom post types.
* Caching methods: DB, HDD, APC and Memcached.
* “Flush Cache” button in the WordPress toolbar.
* Ready for WordPress Multisite.
* Optional compression of HTML markup.
* White lists for posts and user agents.
* Manual and automatic cache reset.
* Automatic cache management.
* Dashboard widget for cached objects.
* Settings for Apache and Nginx servers.
* Extendability via hooks/filters.

### Support ###
* Community support via the [support forums on wordpress.org](https://wordpress.org/support/plugin/cachify/)
* We don’t handle support via e-mail, Twitter, GitHub issues etc.

### Contribute ###
* Active development of this plugin is handled [on GitHub](https://github.com/pluginkollektiv/cachify).
* Pull requests for documented bugs are highly appreciated.
* If you think you’ve found a bug (e.g. you’re experiencing unexpected behavior), please post at the [support forums](https://wordpress.org/support/plugin/cachify/) first.
* If you want to help us translate this plugin you can do so [on WordPress Translate](https://translate.wordpress.org/projects/wp-plugins/cachify/).

### Credits ###
* Author: [Sergej Müller](https://sergejmueller.github.io)
* Maintainers: [pluginkollektiv](https://pluginkollektiv.org)


## Installation ##
* If you don’t know how to install a plugin for WordPress, [here’s how](https://wordpress.org/support/article/managing-plugins/#installing-plugins).

### Requirements ###
* PHP 5.2.4 or greater
* WordPress 3.8 or greater
* APC 3.1.4 or greater (optional)
* Memcached in Nginx (optional)


## Frequently Asked Questions ##

### No cache expiration option while using HDD cache? ###
The cache expiration can not be considered due to technical reasons. If the cache stock has to be emptied at certain time intervals, then it is recommended to call a prepared PHP file by a cronjob.

### PHP Fatal error: Cannot use output buffering in output buffering display handlers in Unknown on line 0 ###
This error message may occur after commissioning the caching plug-in. The hint appears because there are no cache files on the HDD for output. This is probably due to the fact that Cachify could not store files in the cache folder. Please check the write-permissions for the cache folder (found in the WordPress directory *wp-content*) and set them if necessary.

### My Website looks in some parts broken after activating Cachify! ###
Please make sure there is no issue that caused by the Cache minify feature. Just deactivate it or use HTML only. If the issue still exist please feel free to report it at the [support forums](https://wordpress.org/support/plugin/cachify/). With this feature any unnecessary characters such as breaks and HTML comments are removed from the source code.

### Cachify HDD: Character encoding does not work correctly ###
If you use Cachify to store the cache on HDD there is no PHP to run. In the case of misconfigured servers, this can lead to incorrect display of the special characters on web pages. The error can be corrected by an extension of the system file .htaccess: *AddDefaultCharset UTF-8*

### Cachify with CDN support? ###
Currently the caching plugin for WordPress has no connection to a CDN provider. Although the Buzzword CDN (Content Delivery Network) is praised as a performance factor, CDN makes little sense for WordPress websites with a national audience. In this case, a home host could provide the requested files faster than a worldwide CDN service provider because the next node could be far away.

### PHP OPcache as a caching method? ###
Compared to APC (Alternative PHP Cache), PHP OPCache is not able to contain content with custom keys and values. Because of this Cachify can not consider the PHP OPCache as a caching method.

### When does Cachify automaticaly flush its cache? ###
* After publishing new posts
* After publishing new pages
* After publishing new custom post types
* After publishing new sheduled posts (only Cachify DB)
* After updating WordPress
* If you confirm the trash button on the adminbar
* After saving Cachify and wpSEO settings

### Which parts of the website are not cached by default? ###
* Password protected pages
* Feeds
* Trackbacks
* Robots
* Previews
* Mobile-themes (WP-Touch, Carrington, Jetpack Mobile)
* Search
* Error pages

### The cache folder is indexed by search engines! ###
To ensure that Google and other search engines do not index the static contents of the cache folder (otherwise there could be duplicate content), the robots.txt file which is located in the main directory of a WordPress installation should be expanded by disabling the path to the cache file (disallow). This issue should only happen if you use a *static robots.txt* or you changed the *wp-content* location. And so might look a robots.txt:

`User-agent: *
Disallow: */cache/cachify/
Allow: /`

A complete documentation is available in the [GitHub repository Wiki](https://github.com/pluginkollektiv/cachify/wiki).

## Changelog ##

### 2.2.4 ###
* Fixes caching for mixed HTTPS and HTTP setups
* Fixes an issue with the icon styling in the admin toolbar
* Ensures compatibility with the latest WordPress version

### 2.2.3 ###
* New: Generated a POT file
* New: Added German formal translation
* Updated, translated + formatted README.md
* Updated expired link URLs in plugin and languages files
* Updated [plugin authors](https://gist.github.com/glueckpress/f058c0ab973d45a72720)

### 2.2.2 ###
* Fix: parameter return by filter `dashboard_glance_items`
* Generous use of the filter `esc_html`

### 2.2.1 ###
* Fix for the PHP notice "Call to undefined function is_plugin_active_for_network" on WordPress Multisite

### 2.2.0 ###
* Toolbar: Display of the "Flush the cachify cache" button on the frontend
* Toolbar: Controlling the display of the "Flush the cachify cache" button via hook

For the complete changelog, check out our [GitHub repository](https://github.com/pluginkollektiv/cachify).

## Upgrade Notice ##

### 2.3.0 ###
To improve Cachify and make use of new core functions, we decided to drop support for WordPress 4.3 and older. Please make sure your WordPress is always up to date.

## Screenshots ##
1. Cachify Dashboard Widget
2. Cachify settings
3. Flush Cache button in admin bar
