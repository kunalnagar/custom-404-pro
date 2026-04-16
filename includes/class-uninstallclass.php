<?php
/**
 * Handles plugin uninstall.
 *
 * @package Custom_404_Pro
 */

/**
 * Uninstall class.
 */
class UninstallClass {

	/**
	 * Removes all plugin data on uninstall.
	 *
	 * Drops the logs table and deletes the settings entry from wp_options.
	 * The legacy custom_404_pro_options table is also dropped if it still exists
	 * (e.g. the migration had not run before uninstall).
	 */
	public static function uninstall() {
		global $wpdb;

		// Remove plugin settings from wp_options.
		delete_option( Helpers::OPTION_KEY );

		// Drop the logs table.
		$table_logs = $wpdb->prefix . 'custom_404_pro_logs';
		$wpdb->query( 'DROP TABLE IF EXISTS ' . $table_logs ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

		// Drop the legacy options table if the migration had not run yet.
		$table_options = $wpdb->prefix . 'custom_404_pro_options';
		$wpdb->query( 'DROP TABLE IF EXISTS ' . $table_options ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}
}
