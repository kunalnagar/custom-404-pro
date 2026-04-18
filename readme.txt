=== Custom 404 Pro ===
Contributors: kunalnagar
Donate link: https://www.paypal.me/kunalnagar88/10
Tags: 404, redirect, custom 404, error page, logging
Requires at least: 3.0.1
Tested up to: 6.9.4
Stable tag: 3.12.9
Requires PHP: 7.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Take control of every 404 on your site — redirect visitors to a custom page or URL, log what broke, and get notified when it matters.

== Description ==

**Custom 404 Pro** replaces WordPress's default 404 behaviour with a proper redirect. Instead of leaving visitors on a dead-end error page, you can send them to any page on your site or an external URL — with the HTTP status code of your choice.

= Redirect Modes =

* **WordPress Page** — pick any published page from a dropdown; the plugin redirects to it automatically.
* **Custom URL** — enter any absolute URL to redirect 404s off-site or to a specific path.
* **HTTP Status Code** — choose 301, 302, 307, or 308 to match your SEO or caching requirements.

= 404 Logging =

When logging is enabled, the plugin records every 404 hit to a database table so you can see exactly what is broken:

* Request path
* Visitor IP address (can be disabled for privacy/GDPR compliance)
* Referrer URL
* User agent
* Timestamp

Logs are searchable and can be deleted individually, in bulk, or all at once. They can also be exported as a CSV file.

= Email Notifications =

Optionally receive an admin email each time a 404 is logged. Designed for low-traffic monitoring — if you expect high 404 volume, keep this off to avoid inbox flooding.

= Multisite Support =

Works correctly on WordPress Multisite installations — activation creates the logs table for each site in the network.

= Multilingual Support =

Compatible with **Polylang** and **WPML**. The redirect page is resolved to the correct language variant for the current visitor automatically.

== Installation ==

1. Upload the `custom-404-pro` folder to the `/wp-content/plugins/` directory, or install it directly from the WordPress Plugin Directory.
2. Activate the plugin from the **Plugins** screen.
3. Go to **Custom 404 Pro → Settings → Global Redirect** and choose a redirect mode (WordPress Page or Custom URL).
4. Optionally, go to **Custom 404 Pro → Settings → General** to enable logging and email notifications.

== Frequently Asked Questions ==

= Does this plugin work with page caching plugins? =

Yes, but make sure your caching plugin is not caching 404 responses. If it is, the redirect may not fire. Check your caching plugin's exclusion settings and add 404 status codes or the affected paths to the exclusion list.

= Why is the 404 redirect not working with the Divi theme? =

Some users have reported a conflict with the Divi theme. Try switching to a default WordPress theme to confirm the plugin is working, then disable other plugins one by one to isolate the conflict.

= Can I disable IP logging for GDPR compliance? =

Yes. Go to **Settings → General** and uncheck **Log IP**. All future log entries will record `N/A` instead of the visitor's IP address. Existing entries are not modified.

= Why are my settings not saving after a reinstall? =

Always uninstall the plugin from the **Plugins** screen (do not delete the folder directly from the server). Deleting the folder bypasses the uninstall hook and leaves orphaned data in the database. Reinstalling over stale data can cause unexpected behaviour.

= How do I report a bug or request a feature? =

Please open an issue on [GitHub](https://github.com/kunalnagar/custom-404-pro/issues). The WordPress.org support forum is not monitored.

== Screenshots ==

1. 404 Logs table — searchable, exportable, with bulk-delete support
2. Global Redirect settings — choose a WordPress page or a custom URL
3. General settings — logging, email notifications, IP recording, and redirect status code

== Changelog ==

= 3.12.9 =
* Migrate plugin settings from a custom database table to native wp_options for better compatibility and performance

= 3.12.8 =
* Fix IP logging toggle not persisting correctly due to positional row access

= 3.12.7 =
* Fix WPML/Polylang settings overwriting each other when using per-language domains

= 3.12.6 =
* Add load_plugin_textdomain support for translations

= 3.12.5 =
* Add Polylang and WPML support for 404 redirect page

= 3.12.4 =
* Enforce full WordPress coding standards: PHPDoc comments, input sanitization, file naming convention

= 3.12.3 =
* Improve codebase to meet WordPress coding standards

= 3.12.2 =
* Fix PHP 8.2+ dynamic property deprecation warnings

= 3.12.1 =
* Security: Remediate SQL injection and CSRF vulnerabilities (CVE-2025-9947)
* Update tested up to WordPress 6.9.4

= 3.12.0 =
* Support WordPress 6.6

= 3.11.3 =
* Remove extra plugin tags (only 5 permitted on WP)
* Update contact info

= 3.11.2 =
* Fix vuln in admin notices

= 3.11.1 =
* Fix broken Delete logs link

= 3.11.0 =
* Support WordPress 6.5

= 3.10.1 =
* Fix XSS in Logs page

= 3.10.0 =
* Support WordPress 6.4

= 3.9.0 =
* Support WordPress 6.3

= 3.8.2 =
* Fix logs vuln

= 3.8.1 =
* Fix Search vuln

= 3.8.0 =
* Support WordPress 6.2

= 3.7.4 =
* Fix SQL injection

= 3.7.3 =
* Fix vulnerabilities

= 3.7.2 =
* Fix CSRF vulnerability in Logs table

= 3.7.1 =
* Fix path vulnerability

= 3.7.0 =
* Support WordPress 6.1

= 3.6.0 =
* Support WordPress 6.0

= 3.5.0 =
* Support WordPress 5.9

= 3.4.0 =
* Support WordPress 5.8

= 3.3.0 =
* Add Multisite Support

= 3.2.21 =
* Support WordPress 5.7

= 3.2.20 =
* Support WordPress 5.6

= 3.2.19 =
* Support WordPress 5.5

= 3.2.18 =
* Integrate GitHub actions

= 3.2.17 =
* Bump version to support 5.4

= 3.2.16 =
* Bump version to support 5.3.2

= 3.2.15 =
* Bump version to support 5.3.1

= 3.2.14 =
* Update Readme to include FAQ

= 3.2.13 =
* Remove upgrader script

= 3.2.12 =
* Updates + Remove Migrate & Reset Tabs

= 3.2.11 =
* Fix Redirect Bug

= 3.2.10 =
* More updates and fixes

= 3.2.9 =
* Fix Reflected XSS in other places according to the WordPress Plugin Notice

= 3.2.8 =
* Fix Reflected XSS

= 3.2.7 =
* Version Bump to support WordPress 5.2

= 3.2.6 =
* Follow WordPress Coding Standards

= 3.2.5 =
* Update from v2 to v3 for all users

= 3.2.4 =
* Error Logging

= 3.2.3 =
* [BUGFIX] Migrate logs changed to 500

= 3.2.2 =
* [NEW] Migrate Tab: Migrate Logs from Plugin version < 3.0.0 to the new logging system
* [BUGFIX] Typo in Reset Tab when deleting old logs

= 3.2.1 =
* [NEW] Bulk Action: Delete All Logs now available

= 3.2.0 =
* Exports Logs as CSV
* Better model for showing Admin Notices
* Validating URL (required and structure) when URL mode chosen for redirection
* General cleanup

= 3.1.1 =
* Fix Log IP default setting

= 3.1.0 =
* Logging IP is now optional

= 3.0.5 =
* Fix Upgrader function bug

= 3.0.4 =
* Fix Settings not saving Bug

= 3.0.3 =
* Fix Uninstall Bug

= 3.0.2 =
* Streamlining the upgrade process

= 3.0.0 =
* Complete re-write from the ground-up with a new logging mechanism and better base for future development

= 2.1.1 =
* Add Referer so users know where the 404 came from

= 2.1.0 =
* Cleanup on uninstall
* Email blog title
* Fix unnecessary CSS and JS loading

= 2.0.3 =
* Disable logging by default

= 2.0.2 =
* Fixed Donate Links

= 2.0.1 =
* Small bugfix while clearing logs

= 2.0.0 =
* Better feedback while Clearing Logs
* Added 404 Option to Log Type

= 1.4.0 =
* Option to Clear Logs
* Option to Stop Logging

= 1.3.12 =
* Fixed github issue #3

= 1.3.10 =
* Fixed some bugs

= 1.3.9 =
* Redefined Log Filters with User Agent API

= 1.3.8 =
* Added User Agent Filter

= 1.3.0 =
* Changed entire plugin to a Custom Post Type Layout
* More structure to the plugin, better code

= 1.0.0 =
* Initial Release
