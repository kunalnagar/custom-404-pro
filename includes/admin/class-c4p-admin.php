<?php
/**
 * Custom404Pro Admin
 *
 * @class C4P_Admin
 * @package Custom404Pro/Admin
 * @version  3.2.7
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class C4P_Admin {

    /**
     * Admin Constructor.
     */
    public function __construct() {
        add_action('init', array($this, 'includes'));
    }

    /**
     * Include any classes we need within admin.
     */
    public function includes() {
        include_once dirname(__FILE__) . '/class-wc-admin-menus.php';
    }

}