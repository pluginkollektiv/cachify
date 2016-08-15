# Cachify #
* Contributors:      pluginkollektiv
* Donate link:       https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=LG5VC9KXMAYXJ
* Tags:              apc, cache, caching, performance
* Requires at least: 3.8
* Tested up to:      4.6
* Stable tag:        2.2.4
* License:           GPLv2 or later
* License URI:       http://www.gnu.org/licenses/gpl-2.0.html

Smart, efficient cache solution for WordPress. Use DB, HDD, APC or Memcached for storing your blog pages. Make WordPress faster!

## Description ##
*Cachify* optimizes your page loads by caching posts, pages and custom post types as static content. You can choose between caching via database, on the web server’s hard drive (HDD), or—thanks to APC (Alternative PHP Cache)—directly in the web server’s system cache. Whenever a page or post is loaded, it can be pulled directly from the cache. The amount of database queries and PHP requests will dramatically decrease towards zero, depending on the caching method you chose.

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

> #### Auf Deutsch? ####
> Für eine ausführliche Dokumentation besuche bitte das [Cachify-Wiki](https://github.com/pluginkollektiv/cachify/wiki).
>
> **Community-Support auf Deutsch** erhältst du in einem der [deutschsprachigen Foren](https://de.forums.wordpress.org/forum/plugins); im [Plugin-Forum für Cachify](https://wordpress.org/support/plugin/cachify) wird, wie in allen Plugin-Foren auf wordpress.org, ausschließlich **Englisch** gesprochen.

### Support ###
* Community support via the [support forums on wordpress.org](https://wordpress.org/support/plugin/cachify)
* We don’t handle support via e-mail, Twitter, GitHub issues etc.

### Contribute ###
* Active development of this plugin is handled on GitHub.
* Pull requests for documented bugs are highly appreciated.
* If you think you’ve found a bug (e.g. you’re experiencing unexpected behavior), please post at the [support forums](https://wordpress.org/support/plugin/cachify) first.

### Credits ###
* Author: [Sergej Müller](https://sergejmueller.github.io/)
* Maintainers: [pluginkollektiv](http://pluginkollektiv.org)

## Installation ##
* If you don’t know how to install a plugin for WordPress, [here’s how](http://codex.wordpress.org/Managing_Plugins#Installing_Plugins).

### Requirements ###
* PHP 5.2.4 or greater
* WordPress 3.8 or greater
* APC 3.1.4 or greater (optional)
* Memcached in Nginx (optional)


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
* Fix: Parameter-Rückgabe beim Filter `dashboard_glance_items`
* Großzügige Anwendung des Filters `esc_html`

### 2.2.1 ###
* Fix für die Meldung "Call to undefined function is_plugin_active_for_network" in WordPress-Multisite

### 2.2.0 ###
* Werkzeugleiste: Anzeige des "Cache leeren" Buttons im Frontend der Website
* Werkzeugleiste: Steuerung der Anzeige des "Cache leeren" Buttons via Hook

For the complete changelog, check out our [GitHub repository](https://github.com/pluginkollektiv/cachify).

## Upgrade Notice ##

### 2.2.4 ###
This is mainly a maintenance release ensuring compatibility with the latest version of WordPress. Expect bigger changes in 2.3.0 soon!

## Screenshots ##
1. Cachify Dashboard Widget
