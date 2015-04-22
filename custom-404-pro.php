<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://kunalnagar.in
 * @since             1.0.0
 * @package           Custom_404_Pro
 *
 * @wordpress-plugin
 * Plugin Name: Custom 404 Pro
 * Plugin URI: https://github.com/kunalnagar/custom-404-pro
 * Description: Override the default 404 page with any page from the Admin Panel.
 * Version: 1.0.0
 * Author: Kunal Nagar
 * Author URI: http://kunalnagar.in
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: custom-404-pro
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-custom-404-pro-activator.php
 */
function activate_custom_404_pro() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-custom-404-pro-activator.php';
	Custom_404_Pro_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-custom-404-pro-deactivator.php
 */
function deactivate_custom_404_pro() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-custom-404-pro-deactivator.php';
	Custom_404_Pro_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_custom_404_pro' );
register_deactivation_hook( __FILE__, 'deactivate_custom_404_pro' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-custom-404-pro.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_custom_404_pro() {

	$plugin = new Custom_404_Pro();
	$plugin->run();

}
run_custom_404_pro();
