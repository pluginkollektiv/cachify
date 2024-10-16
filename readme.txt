# Cachify #
* Contributors:      pluginkollektiv
* Donate link:       https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=TD4AMD2D8EMZW
* Tags:              cache, caching, performance, optimize, speed
* Requires at least: 4.7
* Tested up to:      6.6
* Requires PHP:      5.6
* Stable tag:        2.4.0
* License:           GPLv2 or later
* License URI:       https://www.gnu.org/licenses/gpl-2.0.html

Smart, efficient cache solution for WordPress. Use DB, HDD, Redis or Memcached for storing your blog pages. Make WordPress faster!

## Description ##
*Cachify* optimizes your page loads by caching posts, pages and custom post types as static content. You can choose between caching via database, on the web server’s hard drive (HDD), Memcached (only on Nginx) or Redis. Whenever a page or post is loaded, it can be pulled directly from the cache. The amount of database queries and PHP requests will dramatically decrease towards zero, depending on the caching method you chose.

### Features ###
* Works with custom post types.
* Caching methods: DB, HDD, Redis and Memcached.
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
* PHP 5.6 or greater
* WordPress 4.7 or greater
* Memcached in Nginx (optional)
* Redis (optional, via the phpredis module)


## Frequently Asked Questions ##

### No cache expiration option while using HDD cache? ###
The cache expiration can not be considered due to technical reasons. If the cache stock has to be emptied at certain time intervals, then it is recommended to call a prepared PHP file by a cronjob.

### PHP Fatal error: Cannot use output buffering in output buffering display handlers in Unknown on line 0 ###
This error message may occur after commissioning the caching plugin. The hint appears because there are no cache files on the HDD for output. This is probably due to the fact that Cachify could not store files in the cache folder. Please check the write-permissions for the cache folder (found in the WordPress directory *wp-content*) and set them if necessary.

### My Website looks in some parts broken after activating Cachify! ###
Please make sure there is no issue that caused by the Cache minify feature. Just deactivate it or use HTML only. If the issue still exist please feel free to report it at the [support forums](https://wordpress.org/support/plugin/cachify/). With this feature any unnecessary characters such as breaks and HTML comments are removed from the source code.

### Cachify HDD: Character encoding does not work correctly ###
If you use Cachify to store the cache on HDD there is no PHP to run. In the case of misconfigured servers, this can lead to incorrect display of the special characters on web pages. The error can be corrected by an extension of the system file .htaccess: *AddDefaultCharset UTF-8*

### Cachify with CDN support? ###
Currently the caching plugin for WordPress has no connection to a CDN provider. Although the Buzzword CDN (Content Delivery Network) is praised as a performance factor, CDN makes little sense for WordPress websites with a national audience. In this case, a home host could provide the requested files faster than a worldwide CDN service provider because the next node could be far away.


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

A complete documentation is available in the [online handbook](https://cachify.pluginkollektiv.org/documentation/).

## Changelog ##

### 2.4.0 ###

Requires PHP 5.6 and WordPress 4.7 or above

* New: introduce Redis (also KeyDB and Valkey) support using the _phpredis_ module (#253), (#252, props @newtovaux)
* New: add `cachify_modify_output` filter
* New: add `cachify_create_gzip_files` to disable creation of static GZip files (#262, props @angcl)
* New: add hooks `cachify_removed_cache_by_url` and `cachify_flushed_total_cache` for additional actions after clearing (#294, props @ouun)
* Removed: APC support (#304)
* Enhance: adjust styling for setup instructions (#215, props @timse201)
* Enhance: update hooks for Multisite initialization in WordPress 5.1 and above (#246, props @ouun)
* Enhance: rework flush hooks and add some third-party triggers for Autoptimize and WooCommerce (#225, props @timse201)
* Enhance: clean up some internal error suppressions (#256)
* Enhance: inform user on cache clear in admin bar (#257, props @angcl)
* Enhance: do not flush the cache for post revisions (#261, props @angcl)
* Enhance: prevent unnecessary cache clearing in some cases (#223) (#224, props @timse201)
* Enhance: remove empty directories when clearing the HDD cache (#289)
* Enhance: introduce common interface for caching backends (#298, props @lloc)
* Enhance: enhance examples for .htaccess and nginx configuration (#302)
* Enhance: show admin notice instead of silent fallback to DB cache, if selected backend is unavailable (#305)
* Enhance: disable gzip creation of required PHP extension is missing (#308)
* Enhance: various internal code clean ups
* Fix: invalidate cache when permalink changes (#285, #286, props @raffaelj)
* Fix: remove empty directories when pruning the HDD cache (#289)
* Fix: correctly add user-agent to robots.txt (#282) (#283)
* Fix: exclude _sitemap.xml_ from caching (#242) (#254)
* Fix: prevent cache generation of non-GET requests (#200) (#258)
* Fix: prevent cache generation of requests with status different from 200 OK (#266) (#267, props @karlkowald)
* Fix: prevent cache generation of non-HTML responses when using content negotiation (#265) (#273, props @Ancocodet)
* Fix: fix styling for various dark mode plugins (#264) (#278)
* Fix: fix SVG markup for icons in dashboard widget (#269, props @Latz)
* Fix: added missing .gz suffix in htaccess (#287) (#291, props @raffaelj)
* Fix: fix some brand names and unify spelling (#297, props @pedro-mendonca)
* Maintenance: Tested up to WordPress 6.6

### 2.3.2 ###
* Fix: enforce WordPress environment for caching modules (#221, props timse201)
* Fix: Remove unnecessary build artifacts from plugin deployment (#226)
* Fix: Fix input sanitization for APC proxy (#240) (#241)
* Maintenance: Remove unused language folder (#214, props timse201)
* Maintenance: Update documentation links (#211, #212, props timse201)
* Maintenance: Update documentation links (#213, props timse201)
* Maintenance: More precise tags in README file (#216, props timse201)
* Maintenance: Tested up to WordPress 5.8

### 2.3.1 ###
* Fix: clean up unused parameter evaluation after publishing a post to prevent PHP notice (#187) (#188)
* Fix: correct minor spelling mistakes (#193, props timse201)
* Fix: update support links (#194, props timse201)

### 2.3.0 ###
* New: WP-CLI integration (#165, props derweili)
* New: `cachify_flush_cache_hooks` filter added to modify all hooks that flush the cache
* New: Flush cache when a user is created / updated / deleted
* New: Flush cache when a term is created / updated / deleted (#169, props derweili)
* New: Cache behavior after post modification is now configurable in plugin settings (#176)
* Enhance: Cache exceptions/User-Agents translation (#52, props timse201)
* Enhance: Readme FAQ (#51, props timse201)
* Enhance: sizeable exclusion boxes + placeholder (#53, props timse201)
* Enhance: FAQ and Support links (#55, props timse201)
* Enhance: Add text caption to "flush cache" button
* Enhance: Icon font converted to SVG (#64)
* Enhance: Improved HDD cache invalidation for hierarchical post types (#71, props Syberspace)
* Enhance: Unified and shortened HTML signature across all caching methods (#108) (#109)
* Security: Tabnabbing prevention (#55, props timse201)
* Maintenance: Tested up to WordPress 5.4

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
* Toolbar: Display of the "Flush the Cachify cache" button on the frontend
* Toolbar: Controlling the display of the "Flush the Cachify cache" button via hook

For the complete changelog, check out our [GitHub repository](https://github.com/pluginkollektiv/cachify).

## Upgrade Notice ##

### 2.3.2 ###
This is a minor maintenance release. It is recommended for all users.

### 2.3.1 ###
This is a minor bug fix release that prevents PHP warnings introduced in 2.3.0. It is recommended for all users.

### 2.3.0 ###
To improve Cachify and make use of new core functions, we decided to drop support for WordPress 4.3 and older. Please make sure your WordPress is always up to date.

## Screenshots ##
1. Cachify Dashboard Widget
2. Cachify settings
3. Flush Cache button in admin bar
