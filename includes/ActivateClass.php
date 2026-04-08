<?php

class ActivateClass {

	private static function run_activation() {
		global $wpdb;
		$helpers                = Helpers::singleton();
		$is_table_options_query = $wpdb->prepare( "SHOW TABLES LIKE %s", $wpdb->prefix . $helpers->table_options ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$is_table_logs_query    = $wpdb->prepare( "SHOW TABLES LIKE %s", $wpdb->prefix . $helpers->table_logs ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$is_table_options       = $wpdb->query( $is_table_options_query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$is_table_logs          = $wpdb->query( $is_table_logs_query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		if ( empty( $is_table_options ) && empty( $is_table_logs ) ) {
			self::create_tables();
			self::initialize_options();
		}
	}

	public static function activate() {
		global $wpdb;
		if ( current_user_can( 'manage_options' ) ) {
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
	}

	public static function create_tables() {
		global $wpdb;
		$helpers       = Helpers::singleton();
		$table_options = $wpdb->prefix . $helpers->table_options;
		$table_logs    = $wpdb->prefix . $helpers->table_logs;
		if ( current_user_can( 'manage_options' ) ) {
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
			$sql_options     = "CREATE TABLE $table_options (
    			id mediumint(9) NOT NULL AUTO_INCREMENT,
    			name text,
    			value text,
    		  	created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    		  	updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    		  	PRIMARY KEY (id)
    		) $charset_collate;";
			include_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql_logs );
			dbDelta( $sql_options );
		}
	}

	public static function initialize_options() {
		global $wpdb;
		if ( current_user_can( 'manage_options' ) ) {
			$helpers = Helpers::singleton();
			$helpers->initialize_table_options();
		}
	}
}
