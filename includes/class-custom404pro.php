<?php
/**
 * Custom 404 Pro setup.
 *
 * @package Custom404Pro
 * @since 3.2.7
 */

defined( 'ABSPATH' ) || exit;

/**
 * Main Custom 404 Pro Class.
 *
 * @class Custom404Pro
 */
final class Custom404Pro {

    /**
     * Custom 404 Pro version.
     *
     * @var string
     */
    public $version = '3.2.7';

    /**
     * The single instance of the class.
     *
     * @var Custom404Pro
     * @since 3.2.7
     */
    protected static $_instance = null;

    /**
     * Main Custom404Pro Instance.
     *
     * Ensures only one instance of Custom404Pro is loaded or can be loaded.
     *
     * @since 3.2.7
     * @static
     * @return Custom404Pro - Main instance.
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Custom404Pro Constructor.
     */
    public function __construct() {
        $this->define_constants();
        $this->includes();
        $this->init_hooks();
    }

    /**
     * Define Custom404Pro Constants.
     */
    private function define_constants() {
        $this->define('C4P_ABSPATH', dirname(C4P_PLUGIN_FILE) . '/');
        $this->define('C4P_PLUGIN_BASENAME', plugin_basename(C4P_PLUGIN_FILE));
    }

    /**
     * Include required files used in admin and frontend.
     */
    public function includes() {
        include_once C4P_ABSPATH . 'includes/c4p-core-functions.php';
        include_once C4P_ABSPATH . 'includes/class-c4p-install.php';
    }

    /**
     * Hooks into actions and filters.
     *
     * @since 3.2.7
     */
    private function init_hooks() {
        register_activation_hook( C4P_PLUGIN_FILE, array( 'C4P_Install', 'install' ) );
        register_uninstall_hook( C4P_PLUGIN_FILE, array( 'C4P_Install', 'uninstall' ) );

    }

    /**
     * Define constant if not already set.
     *
     * @param string      $name  Constant name.
     * @param string|bool $value Constant value.
     */
    private function define( $name, $value ) {
        if ( ! defined( $name ) ) {
            define( $name, $value );
        }
    }

    /**
     * What type of request is this?
     *
     * @param  string $type admin, ajax, cron or frontend.
     * @return bool
     */
    private function is_request( $type ) {
        switch ( $type ) {
            case 'admin':
                return is_admin();
            case 'ajax':
                return defined( 'DOING_AJAX' );
            case 'cron':
                return defined( 'DOING_CRON' );
            case 'frontend':
                return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
        }
    }

}
