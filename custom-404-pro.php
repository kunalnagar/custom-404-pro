<?php

/*
Plugin Name: Custom 404 Pro
Plugin URI: https://wordpress.org/plugins/custom-404-pro/
Description: Override the default 404 page with any page or a custom URL from the Admin Panel.
Version: 3.2.17
Author: Kunal Nagar
Author URI: https://kunalnagar.in
License: GPL-2.0+
License URI: http://www.gnu.org/licenses/gpl-2.0.txt
*/

if ( ! defined( 'WPINC' ) ) {
	die;
}

function activate_custom_404_pro() {
	include_once plugin_dir_path( __FILE__ ) . 'includes/ActivateClass.php';
	ActivateClass::activate();
}

function deactivate_custom_404_pro() {
	include_once plugin_dir_path( __FILE__ ) . 'includes/DeactivateClass.php';
	DeactivateClass::deactivate();
}

function uninstall_custom_404_pro() {
	include_once plugin_dir_path( __FILE__ ) . 'includes/UninstallClass.php';
	UninstallClass::uninstall();
}

register_activation_hook( __FILE__, 'activate_custom_404_pro' );
register_deactivation_hook( __FILE__, 'deactivate_custom_404_pro' );
register_uninstall_hook( __FILE__, 'uninstall_custom_404_pro' );

require plugin_dir_path( __FILE__ ) . 'includes/PluginClass.php';
require plugin_dir_path( __FILE__ ) . 'admin/Helpers.php';

function run_custom_404_pro() {
	 Helpers::singleton();
	new PluginClass();
}

run_custom_404_pro();
