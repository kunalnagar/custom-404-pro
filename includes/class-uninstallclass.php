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
	 * Drops plugin database tables on uninstall.
	 */
	public static function uninstall() {
		global $wpdb;
		if ( current_user_can( 'manage_options' ) ) {
			$table_logs    = $wpdb->prefix . 'custom_404_pro_logs';
			$sql_logs      = "DROP TABLE IF EXISTS $table_logs"; // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$table_options = $wpdb->prefix . 'custom_404_pro_options';
			$sql_options   = "DROP TABLE IF EXISTS $table_options"; // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$wpdb->query( $sql_logs ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
			$wpdb->query( $sql_options ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		}
	}
}
