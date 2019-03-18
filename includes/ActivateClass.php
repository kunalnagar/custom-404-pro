<?php

class ActivateClass {

    public static function activate() {
        global $wpdb;
        $helpers = Helpers::singleton();
        $is_table_options_query = "SHOW TABLES LIKE '" . $helpers->table_options . "';";
        $is_table_logs_query = "SHOW TABLES LIKE '" . $helpers->table_logs . "';";
        $is_table_options = $wpdb->query($is_table_options_query);
        $is_table_logs = $wpdb->query($is_table_logs_query);
        if(empty($is_table_options) && empty($is_table_logs)) {
            self::create_tables();
            self::initialize_options();
        }
	}

	static function create_tables() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
        $helpers = Helpers::singleton();
		$sql_logs = "CREATE TABLE $helpers->table_logs (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			ip text,
			path text,
			referer text,
			user_agent text,
			created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		  	PRIMARY KEY (id)
		) $charset_collate;";
		$sql_options = "CREATE TABLE $helpers->table_options (
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

	static function initialize_options() {
		global $wpdb;
        $helpers = Helpers::singleton();
        $helpers->initialize_table_options();
	}
}