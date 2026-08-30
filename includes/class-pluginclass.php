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
	 * Brings an existing installation up to date on first load after an upgrade.
	 *
	 * Users who update the plugin without deactivating it first never trigger
	 * register_activation_hook, so everything the activation path sets up has to
	 * be repeated here: the table schema, the legacy options migration, and the
	 * daily prune cron event.
	 *
	 * The routine is gated twice.
	 *
	 * First on request context. Applying schema changes means an ALTER TABLE,
	 * which on a very large logs table takes seconds and blocks the request that
	 * triggers it. Restricting it to admin, cron and WP-CLI requests means an
	 * administrator absorbs that cost on their own page load rather than a
	 * random visitor hitting a 404. The plugin's redirect and logging paths do
	 * not depend on the new schema, so a front-end request that skips the
	 * upgrade still behaves correctly.
	 *
	 * Then on a stored db version, so it runs once per released version rather
	 * than on every request. On Multisite the option is per-site, so each site
	 * in the network upgrades independently on its own first admin request.
	 *
	 * @since 3.12.9
	 * @since 3.16.0 Runs dbDelta so schema changes reach existing installs, and
	 *               no longer runs on front-end requests.
	 */
	public function maybe_upgrade() {
		if ( ! defined( 'CUSTOM_404_PRO_VERSION' ) ) {
			return;
		}
		if ( get_option( 'custom_404_pro_db_version' ) === CUSTOM_404_PRO_VERSION ) {
			return;
		}
		if ( ! self::is_upgrade_context() ) {
			return;
		}

		include_once plugin_dir_path( __FILE__ ) . 'class-activateclass.php';

		// Applies any schema changes (new columns, indexes) to existing tables.
		ActivateClass::create_tables();
		ActivateClass::maybe_migrate_legacy_options();

		if ( ! wp_next_scheduled( 'custom_404_pro_prune_logs' ) ) {
			wp_schedule_event( time(), 'daily', 'custom_404_pro_prune_logs' );
		}

		update_option( 'custom_404_pro_db_version', CUSTOM_404_PRO_VERSION );
	}

	/**
	 * Whether the current request is one that may carry out a schema upgrade.
	 *
	 * @since 3.16.0
	 * @return bool True for admin, cron and WP-CLI requests.
	 */
	public static function is_upgrade_context(): bool {
		if ( is_admin() ) {
			return true;
		}
		if ( function_exists( 'wp_doing_cron' ) && wp_doing_cron() ) {
			return true;
		}
		return defined( 'WP_CLI' ) && WP_CLI;
	}

	/**
	 * Backwards-compatible alias for maybe_upgrade().
	 *
	 * @deprecated 3.16.0 Use maybe_upgrade() instead. The routine now covers
	 *             schema upgrades as well as the legacy options migration.
	 */
	public function maybe_migrate_legacy_options() {
		$this->maybe_upgrade();
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
		add_action( 'plugins_loaded', array( $this, 'maybe_upgrade' ) );
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
