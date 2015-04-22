<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://kunalnagar.in
 * @since      1.0.0
 *
 * @package    Custom_404_Pro
 * @subpackage Custom_404_Pro/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Custom_404_Pro
 * @subpackage Custom_404_Pro/admin
 * @author     Kunal Nagar <knlnagar@gmail.com>
 */
class Custom_404_Pro_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Custom_404_Pro_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Custom_404_Pro_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/custom-404-pro-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Custom_404_Pro_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Custom_404_Pro_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/custom-404-pro-admin.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Create the Plugin Admin Menu
	 *
	 * @since    1.0.0
	 */
	public function main_admin_menu() {

		add_submenu_page( 'options-general.php', 'Custom 404 Pro', 'Custom 404 Pro', 'manage_options', 'c4p-main', array($this, 'main_admin_menu_display'));

	}

	/**
	 * Plugin Main View
	 *
	 * @since    1.0.0
	 */
	
	public function main_admin_menu_display() {
		include 'partials/custom-404-pro-admin-display.php';
	}

	/**
	 * Save the Selected 404 Page
	 *
	 * @since    1.0.0
	 */
	public function select_page_submit() {
		$page = get_post($_POST['selected_page']);
		update_option('c4p_selected_page', maybe_serialize($page));
		wp_redirect(admin_url('admin.php?page=c4p-main&message=updated'));
	}

	/**
	 * Custom 404 Hook to redirect users to chosen 404 Page
	 *
	 * @since    1.0.0
	 */
	public function custom_404() {
		if(is_404()) {
			$selected_page = maybe_unserialize(get_option('c4p_selected_page'));
			if(!empty($selected_page)) {
				wp_redirect(site_url() . '/' . $selected_page->post_name);	
			}
		}
	}

}
