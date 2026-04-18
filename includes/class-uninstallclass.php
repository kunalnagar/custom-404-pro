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
	 * On Multisite, data is removed from every site in the network. On single-site
	 * installs the cleanup runs once for the current site.
	 */
	public static function uninstall() {
		if ( is_multisite() ) {
			$sites = get_sites( array( 'fields' => 'ids' ) );
			foreach ( $sites as $blog_id ) {
				switch_to_blog( $blog_id );
				self::cleanup_site();
				restore_current_blog();
			}
		} else {
			self::cleanup_site();
		}
	}

	/**
	 * Removes all plugin data for the current site.
	 *
	 * Drops the logs table, deletes the settings and db-version entries from
	 * wp_options, and drops the legacy options table if the migration had not
	 * run before uninstall.
	 */
	private static function cleanup_site() {
		global $wpdb;

		// Remove plugin settings and migration marker from wp_options.
		delete_option( Helpers::OPTION_KEY );
		delete_option( 'custom_404_pro_db_version' );

		// Drop the logs table.
		$table_logs = $wpdb->prefix . 'custom_404_pro_logs';
		$wpdb->query( 'DROP TABLE IF EXISTS ' . $table_logs ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

		// Drop the legacy options table if the migration had not run yet.
		$table_options = $wpdb->prefix . 'custom_404_pro_options';
		$wpdb->query( 'DROP TABLE IF EXISTS ' . $table_options ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}
}
