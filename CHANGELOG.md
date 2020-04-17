# Changelog
All notable changes to this project will be documented in this file. This project adheres to [Semantic Versioning](http://semver.org/).

## 2.3.0
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

## 2.2.4
* Fixes caching for mixed HTTPS and HTTP setups
* Fixes an issue with the icon styling in the admin toolbar
* Ensures compatibility with the latest WordPress version

## 2.2.3
* New: Generated a POT file
* New: Added German formal translation
* Updated, translated + formatted README.md
* Updated expired link URLs in plugin and languages files
* Updated plugin authors

## 2.2.2
* Fix: parameter return by filter dashboard_glance_items
* Generous use of the filter esc_html

## 2.2.1
* Fix for the PHP notice "Call to undefined function is_plugin_active_for_network" on WordPress Multisite

## 2.2.0
* Toolbar: Display of the "Flush the cachify cache" button on the frontend
* Toolbar: Controlling the display of the "Flush the cachify cache" button via hook

## 2.1.9
* Vervollständigung des Cachify-Pfades in `robots.txt`: `Disallow: /wp-content/cache/cachify/`
* *Release-Zeitaufwand (Development & QA): 0,75 Stunden*

## 2.1.8
* HHVM-Unterstützung für die *Memcached* Caching-Methode (Danke, [Ulrich Block](http://www.ulrich-block.de))

## 2.1.7
* Cache-Leerung bei Custom Post Types
* Einführung zusätzlicher Sicherheitsabfragen
* Code-Refactoring

## 2.1.6
* Button "Cache leeren" sichtbar sowohl in WP 3.8 wie WP 3.9

## 2.1.5
* Support für WordPress 3.9
* Button "Cache leeren" sichtbar bei jeder Bildschirmauflösung

## 2.1.4
* Support für WordPress 3.8.1

## 2.1.3
* Manuelle Auswahl: Beim Artikel-Update nur den Seitencache löschen
* Lokalisierung der Plugin-Dateien
* Umbauten am Plugin-Core
* Detaillierter auf [Google+](https://plus.google.com/+SergejMüller/posts/By2PEtRMk8g)

## 2.1.2
* Optimierung für WordPress 3.8
* Option hinzugefügt: Neue Kommentare lösen einen Cache-Reset aus

## 2.1.1
* Hook `cachify_skip_cache` für die Steuerung der Cache-Generierung
* Support für das MP6 Plugin
* Detaillierter auf [Google+](https://plus.google.com/110569673423509816572/posts/S1mpFsG3NZC)

## 2.1.0
* Cache-Leerung bei Plugin-Deaktivierung

## 2.0.9
* Quelltext-Minimierung als Selektbox und Hook
* Interne Umstellung auf Konstanten

## 2.0.7
* Unterstützung für Memcached
* WordPress 3.6 Support
* Cache-Neuaufbau beim Theme-Wechsel
* Quelltext-Optimierungen

## 2.0.6
* Cache-Neuaufbau einer Blogseite nur bei Kommentaren, die freigegeben sind

## 2.0.5
* Cache-Leerung nach einem WordPress-Upgrade
* Keine Cache-Ausgabe für Jetpack Mobile Theme
* Abfrage auf eingeloggte Nutzer bei APC als Caching-Methode
* Änderung der Systemvoraussetzungen
* Cache-Reset nach WordPress-Update

## 2.0.4
* Bessere Trennung der Cache-Gesamtgröße im Dashboard-Widget "Auf einen Blick"

## 2.0.3
* Cache-Leerung beim Veröffentlichen verfügbarer Custom Post Types
* Noindex in der von WordPress generierten `robots.txt` für den Ordner mit HDD-Cache
* Hook `cachify_flush_cache` zum Leeren des Cache aus Drittanwendungen

## 2.0.2
* Unterstützung für WordPress 3.4
* Hochauflösende Icons für iPad & Co.
* Anpassungen für ältere PHP5-Versionen
* Entfernung des Plugin-Icons aus der Sidebar

## 2.0.1
* Verbesserter Autoload-Prozess
* Diverse Umbenennungen der Optionen
* Cache-Neuaufbau bei geplanten Beiträgen (Cachify DB)

## 2.0
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

## 1.5.1
* `zlib.output_compression = Off` für Apache Webserver

## 1.5
* Überarbeitung des Regexp für HTML-Minify
* Reduzierung des Toolbar-Buttons auf das Icon
* Formatierung und Kommentierung des Quelltextes

## 1.4
* Xmas Edition

## 1.3
* Unterstützung für APC (Alternative PHP Cache)
* Umpositionierung des Admin Bar Buttons

## 1.2.1
* Icon für die "Cache leeren" Schaltfläche in der Admin Bar

## 1.2
* Schaltfläche "Cache leeren" in der Adminbar (ab WordPress 3.1)
* `flush_cache` auf public gesetzt, um von [wpSEO](http://wpseo.de "WordPress SEO Plugin") ansprechen zu können
* Ausführliche Tests unter WordPress 3.3

## 1.1
* Interne Prüfung auf fehlerhafte Cache-Generierung
* Anpassungen an der Code-Struktur
* Entfernung der Inline-Hilfe
* Verknüpfung der Online-Hilfe mit Optionen

## 1.0
* Leerung des Cache beim Aktualisieren von statischen Seiten
* Seite mit Plugin-Einstellungen
* Inline-Dokumentation in der Optionsseite
* Ausschluss von Passwort-geschützten Seiten
* WordPress 3.2 Support
* Unterstützung der WordPress Multisite Blogs
* Umstellung auf den template_redirect-Hook (Plugin-Kompatibilität)
* Interne Code-Bereinigung

## 0.9.2
* HTML-Kompression
* Flattr-Link

## 0.9.1
* Cache-Reset bei geplanten Beiträgen
* Unterstützung für das Carrington-Mobile Theme

## 0.9
* Workaround für Redirects

## 0.8
* Blacklist für PostIDs
* Blacklist für UserAgents
* Ausnahme für WP Touch
* Ausgabe des Zeitpunktes der Generierung
* Umbenennung der Konstanten

## 0.7
* Ausgabe des Speicherverbrauchs

## 0.6
* Live auf wordpress.org
