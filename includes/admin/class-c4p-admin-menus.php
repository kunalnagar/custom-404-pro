<?php

class C4P_Admin_Menus {

    public function __construct() {
        $this->includes();
        $this->init_hooks();
    }

    private function includes() {
        include_once dirname(__FILE__) . '/class-c4p-admin-logs';
        include_once dirname(__FILE__) . '/class-c4p-admin-settings';
        include_once dirname(__FILE__) . '/class-c4p-admin-migrate';
        include_once dirname(__FILE__) . '/class-c4p-admin-reset';
        include_once dirname(__FILE__) . '/class-c4p-admin-about';
    }

    private function init_hooks() {
        add_action('admin_menu', array($this, 'admin_menu'));
    }

    public function admin_menu() {
        add_menu_page('Custom 404 Pro', 'Custom 404 Pro', 'manage_options', 'c4p-main', array($this, 'page_logs', 'dashicons-chart-bar'));
        add_submenu_page( 'c4p-main', 'Logs', 'Logs', 'manage_options', 'c4p-main', array( $this, 'page_logs' ) );
        add_submenu_page( 'c4p-main', 'Settings', 'Settings', 'manage_options', 'c4p-settings', array( $this, 'page_settings' ) );
        add_submenu_page( 'c4p-main', 'Migrate', 'Migrate', 'manage_options', 'c4p-migrate', array( $this, 'page_migrate' ) );
        add_submenu_page( 'c4p-main', 'Reset', 'Reset', 'manage_options', 'c4p-reset', array( $this, 'page_reset' ) );
        add_submenu_page( 'c4p-main', 'About', 'About', 'manage_options', 'c4p-about', array( $this, 'page_about' ) );
    }

    private function page_logs() {
        C4P_Admin_Logs::output();
    }

    private function page_settings() {
        C4P_Admin_Settings::output();
    }

    private function page_migrate() {
        C4P_Admin_Migrate::output();
    }

    private function page_reset() {
        C4P_Admin_Reset::output();
    }

    private function page_about() {
        C4P_Admin_About::output();
    }

}