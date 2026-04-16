<?php
/**
 * Handles plugin activation.
 *
 * @package Custom_404_Pro
 */

/**
 * Activation class.
 */
class ActivateClass {

	/**
	 * Creates the logs table and seeds default settings for a single site.
	 */
	private static function run_activation() {
		self::create_tables();
		self::maybe_migrate_legacy_options();
		self::initialize_options();
	}

	/**
	 * Runs on plugin activation, handling multisite if needed.
	 *
	 * No capability check here — WordPress core already enforces that only users
	 * with activate_plugins can trigger this hook, and the check would silently
	 * break activation via WP-CLI or automated deployment pipelines.
	 */
	public static function activate() {
		if ( is_multisite() ) {
			$sites = get_sites( array( 'fields' => 'ids' ) );
			foreach ( $sites as $blog_id ) {
				switch_to_blog( $blog_id );
				self::run_activation();
				restore_current_blog();
			}
		} else {
			self::run_activation();
		}
	}

	/**
	 * Creates the plugin logs table.
	 *
	 * The legacy options table is no longer created here — settings are stored
	 * in wp_options under the Helpers::OPTION_KEY key.
	 */
	public static function create_tables() {
		global $wpdb;
		$helpers         = Helpers::singleton();
		$table_logs      = $wpdb->prefix . $helpers->table_logs;
		$charset_collate = $wpdb->get_charset_collate();
		$sql_logs        = "CREATE TABLE $table_logs (
    		id mediumint(9) NOT NULL AUTO_INCREMENT,
    		ip text,
    		path text,
    		referer text,
    		user_agent text,
    		created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    		updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    	  	PRIMARY KEY (id)
    	) $charset_collate;";
		include_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql_logs );
	}

	/**
	 * Seeds default settings into wp_options if they have not been set yet.
	 *
	 * Uses add_option() which is a no-op when the key already exists, making
	 * this safe to call on every activation without overwriting saved settings.
	 */
	public static function initialize_options() {
		$helpers = Helpers::singleton();
		add_option( Helpers::OPTION_KEY, $helpers->defaults() );
	}

	/**
	 * Migrates settings from the legacy custom_404_pro_options table to wp_options.
	 *
	 * If the legacy table does not exist this method returns immediately. After a
	 * successful migration the legacy table is dropped. Safe to call multiple
	 * times — subsequent calls are a no-op once the table is gone.
	 */
	public static function maybe_migrate_legacy_options() {
		global $wpdb;
		$legacy_table = $wpdb->prefix . 'custom_404_pro_options';

		// Nothing to migrate if the legacy table does not exist.
		$table_exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $legacy_table ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		if ( ! $table_exists ) {
			return;
		}

		// Already migrated — just clean up the legacy table.
		if ( get_option( Helpers::OPTION_KEY ) ) {
			$wpdb->query( 'DROP TABLE IF EXISTS ' . $legacy_table ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			return;
		}

		// Read legacy rows and build a settings array.
		$rows = $wpdb->get_results( 'SELECT name, value FROM ' . $legacy_table, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		if ( ! empty( $rows ) ) {
			$settings = array();
			foreach ( $rows as $row ) {
				$settings[ $row['name'] ] = $row['value'];
			}
			update_option( Helpers::OPTION_KEY, $settings );
		}

		// Drop the legacy table.
		$wpdb->query( 'DROP TABLE IF EXISTS ' . $legacy_table ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}
}
