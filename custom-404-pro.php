<?php

/**
 * Plugin Name: Custom 404 Pro
 * Plugin URI: https://wordpress.org/plugins/custom-404-pro/
 * Description: Override the default 404 page with any page or a custom URL from the Admin Panel.
 * Version: 3.2.7
 * Author: Kunal Nagar
 * Author URI: https://kunalnagar.in
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 *
 * @package  Custom404Pro
 */

defined( 'ABSPATH' ) || exit;

// Define C4P_PLUGIN_FILE.
if ( ! defined( 'C4P_PLUGIN_FILE' ) ) {
	define( 'C4P_PLUGIN_FILE', __FILE__ );
}

// Include the main Custom404Pro class.
if ( ! class_exists( 'Custom404Pro' ) ) {
	include_once dirname( __FILE__ ) . '/includes/class-custom404pro.php';
}

/**
 * Returns the main instance of Custom404Pro.
 *
 * @since  3.2.7
 * @return Custom404Pro
 */
function C4P() {
	return Custom404Pro::instance();
}

// Global for backwards compatibility.
$GLOBALS['c4p'] = C4P();
