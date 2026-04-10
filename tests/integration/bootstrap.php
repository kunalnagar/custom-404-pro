<?php
/**
 * Integration test bootstrap — loads the real WordPress test suite.
 *
 * Requires bin/install-wp-tests.sh to have been run first so that
 * /tmp/wordpress-tests-lib and /tmp/wordpress exist on the filesystem.
 *
 * @package Custom_404_Pro
 */

$_tests_dir = getenv( 'WP_TESTS_DIR' ) ?: '/tmp/wordpress-tests-lib';

if ( ! file_exists( $_tests_dir . '/includes/functions.php' ) ) {
	echo "Could not find WordPress test suite at '{$_tests_dir}'." . PHP_EOL;
	echo 'Run bin/install-wp-tests.sh first.' . PHP_EOL;
	exit( 1 );
}

// Load WP test functions so we can hook into muplugins_loaded.
require_once $_tests_dir . '/includes/functions.php';

/**
 * Manually loads the plugin classes before WordPress fully initialises.
 *
 * This mirrors what would happen if the plugin were activated. We do not call
 * register_activation_hook here because that hook never fires in the test suite.
 */
function _c404p_manually_load_plugin() {
	$plugin_dir = dirname( __DIR__, 2 );
	require_once $plugin_dir . '/admin/class-helpers.php';
	require_once $plugin_dir . '/admin/class-adminclass.php';
	require_once $plugin_dir . '/admin/class-logsclass.php';
	require_once $plugin_dir . '/includes/class-activateclass.php';
	require_once $plugin_dir . '/includes/class-deactivateclass.php';
	require_once $plugin_dir . '/includes/class-uninstallclass.php';
	require_once $plugin_dir . '/includes/class-pluginclass.php';
}
tests_add_filter( 'muplugins_loaded', '_c404p_manually_load_plugin' );

// Bootstrap WordPress.
require_once $_tests_dir . '/includes/bootstrap.php';
