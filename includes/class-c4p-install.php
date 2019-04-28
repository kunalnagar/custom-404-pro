<?php
/**
 * Installation related functions and actions.
 *
 * @package Custom404Pro/Classes
 * @version 3.2.7
 */

defined( 'ABSPATH' ) || exit;

/**
 * C4P_Install Class.
 */
class C4P_Install {

    /**
     * Install Custom 404 Pro
     */
    public static function install() {
        if(is_blog_installed()) {
            return;
        }
        if('yes' === get_transient('c4p_installing')) {
            return;
        }

        /**
         * Check if plugin tables exist.
         *
         * Check scenarios where user deactivates the plugin and then activates it again.
         * We only want to create tables on a fresh install of the plugin.
         */
        if(!$wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}custom_404_pro_logs'" ) && !$wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}custom_404_pro_options'" )) {
            set_transient( 'c4p_installing', 'yes', MINUTE_IN_SECONDS * 10 );
            self::create_tables();
            delete_transient( 'c4p_installing' );
        }
    }

    /**
     * Set up the database tables which the plugin needs to function.
     */
    private static function create_tables() {
        global $wpdb;
        $wpdb->hide_errors();
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( self::get_schema() );
    }

    /**
     * Get Table Schema.
     *
     * Tables:
     *     custom_404_pro_logs - Table for storing 404 logs
     *     custom_404_pro_options - Table for storing user plugin preferences
     *
     * When adding or removing a table, make sure to update the list of tables in C4P_Install::get_tables()
     *
     * @return string
     */
    private static function get_schema() {
        global $wpdb;
        $collate = '';
        if ( $wpdb->has_cap( 'collation' ) ) {
            $collate = $wpdb->get_charset_collate();
        }
        $tables = "
CREATE TABLE {$wpdb->prefix}custom_404_pro_logs (
    id mediumint(9) NOT NULL AUTO_INCREMENT,
    ip text,
    path text,
    referer text,
    user_agent text,
    created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) $collate;
CREATE TABLE {$wpdb->prefix}custom_404_pro_options (
    id mediumint(9) NOT NULL AUTO_INCREMENT,
    name text,
    value text,
    created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) $collate;
        ";
        return $tables;
    }

    /**
     * Uninstall Custom 404 Pro.
     */
    public static function uninstall() {
        set_transient('c4p_uninstalling', 'yes', MINUTE_IN_SECONDS * 10);
        self::drop_tables();
        delete_transient('c4p_uninstalling');
    }

    /**
     * Drop Custom 404 Pro tables.
     *
     * @return void
     */
    public static function drop_tables() {
        global $wpdb;
        $tables = self::get_tables();
        foreach ( $tables as $table ) {
            $wpdb->query( "DROP TABLE IF EXISTS {$table}" );
        }
    }

    /**
     * Retun a list of Custom 404 Pro tables. Used to make sure all C4P tables are dropped when
     * uninstalling the plugin in a single/multisite environment.
     *
     * @return array C4P tables
     */
    public static function get_tables() {
        global $wpdb;
        $tables = array(
            "{$wpdb->prefix}custom_404_pro_logs",
            "{$wpdb->prefix}custom_404_pro_options"
        );
    }
}
