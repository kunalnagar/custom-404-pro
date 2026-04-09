<?php
/**
 * Test bootstrap: stubs $wpdb so class constructors can reference it without WordPress loaded.
 *
 * @package Custom_404_Pro
 */

global $wpdb;
$wpdb         = new stdClass(); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- intentional stub for unit tests
$wpdb->prefix = 'wp_';

// Simple filter registry used by apply_filters/add_filter stubs below.
$GLOBALS['_test_filters'] = array();

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

require_once dirname( __DIR__ ) . '/admin/class-helpers.php';
require_once dirname( __DIR__ ) . '/admin/class-adminclass.php';
