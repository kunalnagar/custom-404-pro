<?php

class UninstallClass {

	public static function uninstall() {
		global $wpdb;
        if(current_user_can('administrator')) {
    		$table_logs    = $wpdb->prefix . 'custom_404_pro_logs';
    		$sql_logs      = "DROP TABLE IF EXISTS $table_logs";
    		$table_options = $wpdb->prefix . 'custom_404_pro_options';
    		$sql_options   = "DROP TABLE IF EXISTS $table_options";
    		$wpdb->query( $sql_logs );
    		$wpdb->query( $sql_options );
        }
	}
}
