<?php
/**
 * Core plugin class that wires up dependencies and action hooks.
 *
 * @package Custom_404_Pro
 */

/**
 * Plugin class.
 */
class PluginClass {

	/**
	 * The admin class instance.
	 *
	 * @var AdminClass
	 */
	private $plugin_admin;

	/**
	 * Loads dependencies and registers hooks.
	 */
	public function __construct() {
		$this->plugin_admin = '';
		$this->load_dependencies();
		$this->define_admin_hooks();
	}

	/**
	 * Includes and instantiates the admin class.
	 */
	private function load_dependencies() {
		include_once plugin_dir_path( __DIR__ ) . 'admin/class-adminclass.php';
		$this->plugin_admin = new AdminClass();
	}

	/**
	 * Loads the plugin text domain for translation.
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'custom-404-pro', false, dirname( plugin_basename( __DIR__ ) ) . '/languages' );
	}

	/**
	 * Registers all WordPress action hooks for the plugin.
	 */
	private function define_admin_hooks() {
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		add_action( 'admin_menu', array( $this->plugin_admin, 'create_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this->plugin_admin, 'enqueue_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this->plugin_admin, 'enqueue_styles' ) );
		add_action( 'admin_init', array( $this->plugin_admin, 'custom_404_pro_admin_init' ) );
		add_action( 'template_redirect', array( $this->plugin_admin, 'custom_404_pro_redirect' ) );
		add_action( 'admin_notices', array( $this->plugin_admin, 'custom_404_pro_notices' ) );
		add_action( 'admin_post_form-settings-global-redirect', array( $this->plugin_admin, 'form_settings_global_redirect' ) );
		add_action( 'admin_post_form-settings-general', array( $this->plugin_admin, 'form_settings_general' ) );
	}
}
