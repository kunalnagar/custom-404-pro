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
	 * Runs the legacy options table migration on first load after an upgrade.
	 *
	 * Existing installations that update without deactivating/reactivating will
	 * not trigger register_activation_hook. This hook ensures the migration runs
	 * automatically on the first page load of the new version.
	 *
	 * Gated behind a stored db version so the SHOW TABLES check does not fire
	 * on every page load once the migration has been completed.
	 *
	 * @since 3.12.9
	 */
	public function maybe_migrate_legacy_options() {
		if ( defined( 'CUSTOM_404_PRO_VERSION' ) &&
			get_option( 'custom_404_pro_db_version' ) === CUSTOM_404_PRO_VERSION ) {
			return;
		}
		include_once plugin_dir_path( __FILE__ ) . 'class-activateclass.php';
		ActivateClass::maybe_migrate_legacy_options();
		if ( ! wp_next_scheduled( 'custom_404_pro_prune_logs' ) ) {
			wp_schedule_event( time(), 'daily', 'custom_404_pro_prune_logs' );
		}
		if ( defined( 'CUSTOM_404_PRO_VERSION' ) ) {
			update_option( 'custom_404_pro_db_version', CUSTOM_404_PRO_VERSION );
		}
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
		add_action( 'plugins_loaded', array( $this, 'maybe_migrate_legacy_options' ) );
		add_action( 'admin_menu', array( $this->plugin_admin, 'create_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this->plugin_admin, 'enqueue_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this->plugin_admin, 'enqueue_styles' ) );
		add_action( 'admin_init', array( $this->plugin_admin, 'custom_404_pro_admin_init' ) );
		add_action( 'template_redirect', array( $this->plugin_admin, 'custom_404_pro_redirect' ) );
		add_action( 'admin_notices', array( $this->plugin_admin, 'custom_404_pro_notices' ) );
		add_action( 'admin_post_form-settings-global-redirect', array( $this->plugin_admin, 'form_settings_global_redirect' ) );
		add_action( 'admin_post_form-settings-general', array( $this->plugin_admin, 'form_settings_general' ) );
		add_action( 'custom_404_pro_prune_logs', array( $this->plugin_admin, 'run_scheduled_log_prune' ) );
	}
}
