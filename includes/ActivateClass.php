<?php

class ActivateClass {

	public function activate() {
		self::create_tables();
		self::initialize_options();
	}

	function create_tables() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		$table_logs = $wpdb->prefix . "custom_404_pro_logs";
		$sql_logs = "CREATE TABLE $table_logs (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			ip text,
			path text,
			referer text,
			user_agent text,
			created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		  	PRIMARY KEY (id)
		) $charset_collate;";
		$table_options = $wpdb->prefix . "custom_404_pro_options";
		$sql_options = "CREATE TABLE $table_options (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			name text,
			value text,
		  	created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
		  	updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		  	PRIMARY KEY (id)
		) $charset_collate;";
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql_logs);
		dbDelta($sql_options);
	}

	function initialize_options() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		$table_options = $wpdb->prefix . "custom_404_pro_options";
		$sql = "INSERT INTO " . $table_options . " (name, value) VALUES ";
		$sql .= "('mode', NULL),";
		$sql .= "('mode_page', NULL),";
		$sql .= "('mode_url', NULL),";
		$sql .= "('send_email', FALSE),";
		$sql .= "('clear_logs', NULL),";
		$sql .= "('logging_enabled', FALSE),";
		$sql .= "('redirect_error_code', 302)";
		$wpdb->query($sql);
	}
}