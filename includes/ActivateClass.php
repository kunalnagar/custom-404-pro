<?php

class ActivateClass {

    static function _activate() {
        global $wpdb;
        $helpers                = Helpers::singleton();
        $is_table_options_query = "SHOW TABLES LIKE '" . $wpdb->prefix . $helpers->table_options . "';";
        $is_table_logs_query    = "SHOW TABLES LIKE '" . $wpdb->prefix . $helpers->table_logs . "';";
        $is_table_options       = $wpdb->query( $is_table_options_query );
        $is_table_logs          = $wpdb->query( $is_table_logs_query );
        if ( empty( $is_table_options ) && empty( $is_table_logs ) ) {
            self::create_tables();
            self::initialize_options();
        }
    }

	public static function activate() {
		global $wpdb;
        if(current_user_can('administrator')) {
            if(is_multisite()) {
                $sites = get_sites(['fields'=>'ids']);
                foreach ($sites as $blog_id) {
                    switch_to_blog($blog_id);
                    self::_activate();
                    restore_current_blog();
                }
            } else {
                self::_activate();
            }
        }
	}

	public static function create_tables() {
		global $wpdb;
        $helpers                = Helpers::singleton();
        $table_options = $wpdb->prefix . $helpers->table_options;
        $table_logs = $wpdb->prefix . $helpers->table_logs;
        if(current_user_can('administrator')) {
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
    		echo $wpdb->last_error;
    		dbDelta( $sql_options );
    		echo $wpdb->last_error;
        }
	}

	public static function initialize_options() {
		global $wpdb;
        if(current_user_can('administrator')) {
    		$helpers = Helpers::singleton();
    		$helpers->initialize_table_options();
        }
	}
}
