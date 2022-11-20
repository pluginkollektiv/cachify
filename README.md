# Cachify #
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

### Donate
[Donate to us via Paypal](https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=TD4AMD2D8EMZW)

### Credits ###
* Author: [Sergej Müller](https://sergejmueller.github.io)
* Maintainers: [pluginkollektiv](https://pluginkollektiv.org)


## Installation ##
* If you don’t know how to install a plugin for WordPress, [here’s how](https://wordpress.org/support/article/managing-plugins/#installing-plugins).

### Requirements ###
* PHP 5.6 or greater
* WordPress 4.7 or greater
* APC 3.1.4 or greater (optional)
* Memcached in Nginx (optional)


## Frequently Asked Questions ##
Please have a look [in the FAQ pages](https://cachify.pluginkollektiv.org/documentation/faq/).

A complete documentation is available in the [online handbook](https://cachify.pluginkollektiv.org/documentation/).


## Changelog ##
[Changelog](./CHANGELOG.md)
