<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class C4P_Admin_Settings {

    private $active_tab;

    public static function output() {
        $tabs = new stdClass();
        $tabs->global_redirect = array(
            "name" => "Redirect",
            "tab_id" => "global-redirect",
        );
        $tabs->general = array(
            "name" => "General",
            "tab_id" => "general",
        );
        include dirname(__FILE__) . '/views/html-admin-settings.php';
    }
}