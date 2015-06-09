=== Cachify ===
Contributors: sergej.mueller
Tags: apc, cache, caching, performance
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=ZAQUT9RLPW8QN
Requires at least: 3.8
Tested up to: 4.2.2
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html



Smart but efficient cache solution for WordPress. Use DB, HDD, APC or Memcached for storing your blog pages. Make WordPress faster!



== Description ==

*Note: The plugin is available in English and German.*

= Unkompliziert und ausbaufähig =
*Cachify* optimiert Ladezeit der Blogseiten, indem Seiteninhalte in statischer Form wahlweise in der Datenbank, auf der Festplatte (HDD) oder dank APC (Alternative PHP Cache) im Speicher des Webservers abgelegt und beim Seitenaufruf ohne Umwege ausgegeben werden. Die Anzahl der DB-Anfragen und PHP-Anweisungen reduziert sich je nach gewählter Methode gegen Null.


= Pluspunkte =
* Zusammenarbeit mit Custom Post Types
* Speicherungsmethoden: DB, HDD, APC und Memcached
* "Cache leeren" als Schaltfläche in der Admin Bar
* Handbuch als Online-, Kindle- oder Print-Version
* Einsatzbereit für WordPress-Multisite
* Optionale Komprimierung der HTML-Ausgabe
* Ausnahmelisten für Beiträge und User Agents
* Manueller und automatischer Cache-Reset
* Deutschsprachige Oberfläche zum Sofortstart
* Automatisches Management des Cache-Bestandes
* Anzeige der Cache-Belegung auf dem Dashboard
* Konfigurationseinstellungen für Apache- und Nginx-Server
* Zahlreiche Hooks/Filter als Schnittstelle


> #### Cachify-Handbuch
> Als Begleithandbuch und Installationshilfe für *Cachify* wurden ein Kindle-eBook und ein gedrucktes Taschenbuch mit dem Titel „[WordPress Performance](http://www.amazon.de/dp/B0091LDUVA "WordPress Performance"): Beschleunigung der Blogseiten durch Caching“ veröffentlicht. Beide Handbücher beleuchten verfügbare Caching-Methoden, empfehlen Plugin-Einstellungen und liefern wertvolle Tipps & Tricks zur Geschwindigkeitsoptimierung unter WordPress.


= Support =
Fragen rund ums Plugin werden gern per E-Mail beantwortet. Beachtet auch die [Guidelines](https://plus.google.com/+SergejMüller/posts/Ex2vYGN8G2L).


= Systemvoraussetzungen =
* WordPress ab 3.8
* PHP ab 5.2.4
* APC ab 3.1.4 (optional)
* Memcached unter Nginx (optional)


= Unterstützung =
* [PayPal](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=ZAQUT9RLPW8QN)
* [Flattr](https://flattr.com/t/1327625)
* [Wishlist](https://www.amazon.de/registry/wishlist/2U5I7F9649LOJ/)


= Handbuch =
* [Cachify: Caching für WordPress](http://playground.ebiene.de/cachify-wordpress-cache/)


= Website =
* [cachify.de](http://cachify.de "Cachify WordPress Cache")


= Autor =
* [Twitter](https://twitter.com/wpSEO)
* [Google+](https://plus.google.com/110569673423509816572 "Google+")
* [Plugins](http://wpcoder.de "Plugins")




== Changelog ==

= 2.2.2 =
* Fix: Parameter-Rückgabe beim Filter `dashboard_glance_items`
* Großzügige Anwendung des Filters `esc_html`

= 2.2.1 =
* Fix für die Meldung "Call to undefined function is_plugin_active_for_network" in WordPress-Multisite

= 2.2.0 =
* Werkzeugleiste: Anzeige des "Cache leeren" Buttons im Frontend der Website
* Werkzeugleiste: Steuerung der Anzeige des "Cache leeren" Buttons via Hook

= 2.1.9 =
* Vervollständigung des Cachify-Pfades in `robots.txt`: `Disallow: /wp-content/cache/cachify/`
* *Release-Zeitaufwand (Development & QA): 0,75 Stunden*

= 2.1.8 =
* HHVM-Unterstützung für die *Memcached* Caching-Methode (Danke, [Ulrich Block](http://www.ulrich-block.de))

= 2.1.7 =
* Cache-Leerung bei Custom Post Types
* Einführung zusätzlicher Sicherheitsabfragen
* Code-Refactoring

= 2.1.6 =
* Button "Cache leeren" sichtbar sowohl in WP 3.8 wie WP 3.9

= 2.1.5 =
* Support für WordPress 3.9
* Button "Cache leeren" sichtbar bei jeder Bildschirmauflösung

= 2.1.4 =
* Support für WordPress 3.8.1

= 2.1.3 =
* Manuelle Auswahl: Beim Artikel-Update nur den Seitencache löschen
* Lokalisierung der Plugin-Dateien
* Umbauten am Plugin-Core
* Detaillierter auf [Google+](https://plus.google.com/+SergejMüller/posts/By2PEtRMk8g)

= 2.1.2 =
* Optimierung für WordPress 3.8
* Option hinzugefügt: Neue Kommentare lösen einen Cache-Reset aus

= 2.1.1 =
* Hook `cachify_skip_cache` für die Steuerung der Cache-Generierung
* Support für das MP6 Plugin
* Detaillierter auf [Google+](https://plus.google.com/110569673423509816572/posts/S1mpFsG3NZC)

= 2.1.0 =
* Cache-Leerung bei Plugin-Deaktivierung

= 2.0.9 =
* Quelltext-Minimierung als Selektbox und Hook
* Interne Umstellung auf Konstanten

= 2.0.7 =
* Unterstützung für Memcached
* WordPress 3.6 Support
* Cache-Neuaufbau beim Theme-Wechsel
* Quelltext-Optimierungen

= 2.0.6 =
* Cache-Neuaufbau einer Blogseite nur bei Kommentaren, die freigegeben sind

= 2.0.5 =
* Cache-Leerung nach einem WordPress-Upgrade
* Keine Cache-Ausgabe für Jetpack Mobile Theme
* Abfrage auf eingeloggte Nutzer bei APC als Caching-Methode
* Änderung der Systemvoraussetzungen
* Cache-Reset nach WordPress-Update

= 2.0.4 =
* Bessere Trennung der Cache-Gesamtgröße im Dashboard-Widget "Auf einen Blick"

= 2.0.3 =
* Cache-Leerung beim Veröffentlichen verfügbarer Custom Post Types
* Noindex in der von WordPress generierten `robots.txt` für den Ordner mit HDD-Cache
* Hook `cachify_flush_cache` zum Leeren des Cache aus Drittanwendungen

= 2.0.2 =
* Unterstützung für WordPress 3.4
* Hochauflösende Icons für iPad & Co.
* Anpassungen für ältere PHP5-Versionen
* Entfernung des Plugin-Icons aus der Sidebar

= 2.0.1 =
* Verbesserter Autoload-Prozess
* Diverse Umbenennungen der Optionen
* Cache-Neuaufbau bei geplanten Beiträgen (Cachify DB)

= 2.0 =
* Überarbeitung der GUI
* Source Code-Modularisierung
* Cache-Größe auf dem Dashboard
* Festplatte als Ablageort für Cache
* Produktseite online: http://cachify.de
* Cache-Neuaufbau bei Kommentarstatusänderungen
* APC-Anforderungen: APC 3.0.0, empfohlen 3.1.4
* Optional: Kein Cache für kommentierende Nutzer
* Schnellübersicht der Optionen als Inline-Hilfe
* Mindestanforderungen: WordPress 3.1 & PHP 5.1.2

= 1.5.1 =
* `zlib.output_compression = Off` für Apache Webserver

= 1.5 =
* Überarbeitung des Regexp für HTML-Minify
* Reduzierung des Toolbar-Buttons auf das Icon
* Formatierung und Kommentierung des Quelltextes

= 1.4 =
* Xmas Edition

= 1.3 =
* Unterstützung für APC (Alternative PHP Cache)
* Umpositionierung des Admin Bar Buttons

= 1.2.1 =
* Icon für die "Cache leeren" Schaltfläche in der Admin Bar

= 1.2 =
* Schaltfläche "Cache leeren" in der Adminbar (ab WordPress 3.1)
* `flush_cache` auf public gesetzt, um von [wpSEO](http://wpseo.de "WordPress SEO Plugin") ansprechen zu können
* Ausführliche Tests unter WordPress 3.3

= 1.1 =
* Interne Prüfung auf fehlerhafte Cache-Generierung
* Anpassungen an der Code-Struktur
* Entfernung der Inline-Hilfe
* Verknüpfung der Online-Hilfe mit Optionen

= 1.0 =
* Leerung des Cache beim Aktualisieren von statischen Seiten
* Seite mit Plugin-Einstellungen
* Inline-Dokumentation in der Optionsseite
* Ausschluss von Passwort-geschützten Seiten
* WordPress 3.2 Support
* Unterstützung der WordPress Multisite Blogs
* Umstellung auf den template_redirect-Hook (Plugin-Kompatibilität)
* Interne Code-Bereinigung

= 0.9.2 =
* HTML-Kompression
* Flattr-Link

= 0.9.1 =
* Cache-Reset bei geplanten Beiträgen
* Unterstützung für das Carrington-Mobile Theme

= 0.9 =
* Workaround für Redirects

= 0.8 =
* Blacklist für PostIDs
* Blacklist für UserAgents
* Ausnahme für WP Touch
* Ausgabe des Zeitpunktes der Generierung
* Umbenennung der Konstanten

= 0.7 =
* Ausgabe des Speicherverbrauchs

= 0.6 =
* Live auf wordpress.org




== Screenshots ==

1. Cachify auf dem Dashboard