<?php
/**
 * Plugin Name: Custom 404 Pro
 * Plugin URI: https://wordpress.org/plugins/custom-404-pro/
 * Description: Override the default 404 page with any page or a custom URL from the Admin Panel.
 * Version: 3.13.0
 * Author: Kunal Nagar
 * Author URI: https://www.kunalnagar.in
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: custom-404-pro
 * Domain Path: /languages
 *
 * @package Custom_404_Pro
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'CUSTOM_404_PRO_VERSION', '3.13.0' );

/**
 * Runs on plugin activation.
 */
function activate_custom_404_pro() {
	include_once plugin_dir_path( __FILE__ ) . 'includes/class-activateclass.php';
	ActivateClass::activate();
}

/**
 * Runs on plugin deactivation.
 */
function deactivate_custom_404_pro() {
	include_once plugin_dir_path( __FILE__ ) . 'includes/class-deactivateclass.php';
	DeactivateClass::deactivate();
}

/**
 * Runs on plugin uninstall.
 */
function uninstall_custom_404_pro() {
	include_once plugin_dir_path( __FILE__ ) . 'includes/class-uninstallclass.php';
	UninstallClass::uninstall();
}

register_activation_hook( __FILE__, 'activate_custom_404_pro' );
register_deactivation_hook( __FILE__, 'deactivate_custom_404_pro' );
register_uninstall_hook( __FILE__, 'uninstall_custom_404_pro' );

require plugin_dir_path( __FILE__ ) . 'includes/class-pluginclass.php';
require plugin_dir_path( __FILE__ ) . 'admin/class-helpers.php';

/**
 * Initialises and runs the plugin.
 */
function run_custom_404_pro() {
	Helpers::singleton();
	new PluginClass();
}

run_custom_404_pro();
