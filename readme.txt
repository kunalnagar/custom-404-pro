=== Plugin Name ===
Contributors: kunalnagar
Donate link: https://www.paypal.me/kunalnagar/10
Tags: wordpress, 404, 404 error page, 404 link, 404 page, broken link, custom 404, custom 404 error, custom 404 error page, custom 404 page, customize 404, customize 404 error page, customize 404 page, error, error page, missing, page, page not found, page not found error
Requires at least: 3.0.1
Tested up to: 5.2
Stable tag: 3.2.10
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Override the default 404 page with any page from the Admin Panel or a Custom URL.

== Description ==

Allows users to replace the default 404 page with a custom page from the Pages section in the Admin Panel. Or you can specify a complete URL to redirect on 404.

Please open [issues on Github](https://github.com/kunalnagar/custom-404-pro/issues). I will not be using the WordPress.org support area.

= Features =

* Full 404 Page Control
* Record 404 Page Data
* Custom Page Redirect
* Custom URL Redirect

= How does it work? =

* WordPress Page: Choose a custom page from the Admin Panel
* URL: Enter a custom URL for 404
* Stats: List of all 404s

== Installation ==

* Extract the downloaded ZIP file.
* Copy the custom-404-pro folder to the wp-content/plugins directory.
* Activate from the Plugins Section.

== Screenshots ==

1. Activate the plugin from the WordPress Admin Panel
2. 404 Logs
3. Global Settings

== Changelog ==

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
