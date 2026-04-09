<?php
/**
 * Test bootstrap: stubs $wpdb so class constructors can reference it without WordPress loaded.
 *
 * @package Custom_404_Pro
 */

global $wpdb;
$wpdb         = new stdClass(); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- intentional stub for unit tests
$wpdb->prefix = 'wp_';

require_once dirname( __DIR__ ) . '/admin/class-helpers.php';
require_once dirname( __DIR__ ) . '/admin/class-adminclass.php';
