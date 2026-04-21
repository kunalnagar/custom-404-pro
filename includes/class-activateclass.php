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

		// Record the schema version so the plugins_loaded migration check can
		// skip the SHOW TABLES query on every subsequent page load.
		if ( defined( 'CUSTOM_404_PRO_VERSION' ) ) {
			update_option( 'custom_404_pro_db_version', CUSTOM_404_PRO_VERSION );
		}

		// Schedule the daily log-pruning cron event if it is not already registered.
		if ( ! wp_next_scheduled( 'custom_404_pro_prune_logs' ) ) {
			wp_schedule_event( time(), 'daily', 'custom_404_pro_prune_logs' );
		}
	}

	/**
	 * Runs on plugin activation, handling multisite if needed.
	 *
	 * No capability check here — WordPress core already enforces that only users
	 * with activate_plugins can trigger this hook, and the check would silently
	 * break activation via WP-CLI or automated deployment pipelines.
	 *
	 * @since 3.12.9
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
	 *
	 * @since 3.12.9
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
	 *
	 * @since 3.12.9
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
	 *
	 * Values are cast to their correct types (bool, int) using the defaults map
	 * so that string values from the old text columns are not carried forward.
	 *
	 * @since 3.12.9
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

		// Read legacy rows and build a settings array, casting each value to the
		// correct type so that legacy text-column strings do not persist as strings
		// where booleans or integers are expected.
		$rows = $wpdb->get_results( 'SELECT name, value FROM ' . $legacy_table, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		if ( ! empty( $rows ) ) {
			$defaults = ( new Helpers() )->defaults();
			$settings = array();
			foreach ( $rows as $row ) {
				$key   = $row['name'];
				$value = $row['value'];
				if ( array_key_exists( $key, $defaults ) ) {
					if ( is_bool( $defaults[ $key ] ) ) {
						$value = ! empty( $value ) && '0' !== $value;
					} elseif ( is_int( $defaults[ $key ] ) ) {
						$value = (int) $value;
					}
				}
				$settings[ $key ] = $value;
			}
			update_option( Helpers::OPTION_KEY, $settings );
		}

		// Drop the legacy table.
		$wpdb->query( 'DROP TABLE IF EXISTS ' . $legacy_table ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}
}
