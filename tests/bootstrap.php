<?php
/**
 * Test bootstrap: stubs $wpdb so class constructors can reference it without WordPress loaded.
 *
 * @package Custom_404_Pro
 */

global $wpdb;
$wpdb         = new stdClass(); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- intentional stub for unit tests
$wpdb->prefix = 'wp_';

// Simple filter/action registries used by stubs below.
$GLOBALS['_test_filters'] = array();
$GLOBALS['_test_actions'] = array();
$GLOBALS['_load_plugin_textdomain_calls'] = array();

/**
 * Stub for WordPress apply_filters().
 *
 * Runs any callbacks registered via add_filter() for $tag.
 *
 * @param string $tag   The filter hook name.
 * @param mixed  $value The value to filter.
 * @param mixed  ...$args Additional arguments passed to callbacks.
 * @return mixed The filtered value.
 */
function apply_filters( string $tag, $value, ...$args ) { // phpcs:ignore Universal.NamingConventions.NoReservedKeywordParameterNames.matchFound
	if ( isset( $GLOBALS['_test_filters'][ $tag ] ) ) {
		foreach ( $GLOBALS['_test_filters'][ $tag ] as $callback ) {
			$value = $callback( $value, ...$args );
		}
	}
	return $value;
}

/**
 * Stub for WordPress add_filter().
 *
 * @param string   $tag      The filter hook name.
 * @param callable $callback The callback to register.
 */
function add_filter( string $tag, callable $callback ) {
	$GLOBALS['_test_filters'][ $tag ][] = $callback;
}

/**
 * Stub for Polylang pll_get_post().
 *
 * Returns the value of $GLOBALS['_pll_get_post_return']; defaults to false.
 *
 * @param int    $post_id The post ID to translate.
 * @param string $lang    The target language slug.
 * @return int|false
 */
function pll_get_post( int $post_id, string $lang ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
	return $GLOBALS['_pll_get_post_return'] ?? false;
}

/**
 * Stub for Polylang pll_current_language().
 *
 * Returns the value of $GLOBALS['_pll_current_language']; defaults to 'en'.
 *
 * @return string
 */
function pll_current_language(): string {
	return $GLOBALS['_pll_current_language'] ?? 'en';
}

/**
 * Stub for WordPress add_action().
 *
 * @param string   $tag      The action hook name.
 * @param callable $callback The callback to register.
 * @param mixed    ...$args  Additional arguments (priority, accepted_args) — ignored by stub.
 */
function add_action( string $tag, $callback, ...$args ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
	$GLOBALS['_test_actions'][ $tag ][] = array( $callback );
}

/**
 * Stub for WordPress plugin_dir_path().
 *
 * @param string $file Absolute path to the reference file.
 * @return string Trailing-slashed directory path.
 */
function plugin_dir_path( string $file ): string {
	return rtrim( dirname( $file ), DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR;
}

/**
 * Stub for WordPress plugin_basename().
 *
 * Returns a path relative to the plugin root (two levels up from $file).
 *
 * @param string $file Absolute path.
 * @return string Relative plugin basename.
 */
function plugin_basename( string $file ): string {
	$plugins_dir = dirname( dirname( $file ) );
	return ltrim( str_replace( $plugins_dir, '', $file ), DIRECTORY_SEPARATOR );
}

/**
 * Stub for WordPress load_plugin_textdomain().
 *
 * Records the call so tests can assert on the arguments passed.
 *
 * @param string       $domain      Text domain.
 * @param string|false $deprecated  Deprecated — always false.
 * @param string|false $plugin_rel_path Relative path to the languages directory.
 */
function load_plugin_textdomain( string $domain, $deprecated = false, $plugin_rel_path = false ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
	$GLOBALS['_load_plugin_textdomain_calls'][] = array(
		'domain'          => $domain,
		'plugin_rel_path' => $plugin_rel_path,
	);
}

require_once dirname( __DIR__ ) . '/admin/class-helpers.php';
require_once dirname( __DIR__ ) . '/admin/class-adminclass.php';
require_once dirname( __DIR__ ) . '/includes/class-pluginclass.php';
