<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://kunalnagar.in
 * @since      1.0.0
 *
 * @package    Custom_404_Pro
 * @subpackage Custom_404_Pro/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Custom_404_Pro
 * @subpackage Custom_404_Pro/includes
 * @author     Kunal Nagar <knlnagar@gmail.com>
 */
class Custom_404_Pro {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Custom_404_Pro_Loader $loader Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $plugin_name The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $version The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->plugin_name = 'custom-404-pro';
		$this->version     = '1.0.0';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Custom_404_Pro_Loader. Orchestrates the hooks of the plugin.
	 * - Custom_404_Pro_i18n. Defines internationalization functionality.
	 * - Custom_404_Pro_Admin. Defines all hooks for the admin area.
	 * - Custom_404_Pro_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-custom-404-pro-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-custom-404-pro-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-custom-404-pro-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-custom-404-pro-public.php';

		$this->loader = new Custom_404_Pro_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Custom_404_Pro_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Custom_404_Pro_i18n();
		$plugin_i18n->set_domain( $this->get_plugin_name() );

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {
		$plugin_admin = new Custom_404_Pro_Admin( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		// Create Custom 404 Pro Admin Menu
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'create_admin_menu' );

		// Register 404 Logs CPT
		$this->loader->add_action( 'init', $plugin_admin, 'register_logs_cpt' );

		$this->loader->add_action( 'admin_init', $plugin_admin, 'init_options' );

		// Add Custom Fields to 404 Logs CPT
		$this->loader->add_action( 'add_meta_boxes', $plugin_admin, 'logs_add_custom_fields_metabox' );

		// Save Custom Fields Data to 404 Logs CPT
		$this->loader->add_action( 'save_post', $plugin_admin, 'logs_save_custom_fields_data', 1, 2 );

		// Recreate 404 Logs CPT Admin Table Columns for better control
		$this->loader->add_filter( 'manage_edit-c4p_log_columns', $plugin_admin, 'recreate_admin_table_columns' );

		// Add Sorting to 404 Logs CPT Admin Table Columns
		$this->loader->add_filter( 'manage_edit-c4p_log_sortable_columns', $plugin_admin, 'add_sorting_to_admin_table_columns' );

		// Show 404 Logs CPT Values for Individual Columns
		$this->loader->add_filter( 'manage_c4p_log_posts_custom_column', $plugin_admin, 'show_cpt_values_custom_columns', 10, 2 );

		// Save options for Global Redirect Settings (Mode)
		$this->loader->add_action( 'admin_post_select-page-form', $plugin_admin, 'handle_settings_global_redirect_form' );

		// Save options for General Settings
		$this->loader->add_action( 'admin_post_settings-general-form', $plugin_admin, 'handle_settings_general_form' );

		// Main Hook to perform redirections on 404s
		$this->loader->add_filter( 'template_redirect', $plugin_admin, 'custom_404' );

		// Filter Logs using Custom Fields (User Agents etc)
		$this->loader->add_action( 'restrict_manage_posts', $plugin_admin, 'create_log_filters' );
		$this->loader->add_filter( 'pre_get_posts', $plugin_admin, 'get_filter_results' );

		$this->loader->add_action( 'wp_ajax_c4p_clear_logs', $plugin_admin, 'c4p_clear_logs' );
		$this->loader->add_action( 'wp_ajax_c4p_count_logs', $plugin_admin, 'c4p_count_logs' );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Custom_404_Pro_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Custom_404_Pro_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}
}
