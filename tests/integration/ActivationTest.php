<?php
/**
 * Integration tests for plugin activation — table creation and default options.
 *
 * @package Custom_404_Pro
 */

/**
 * Tests that ActivateClass creates the correct database schema and seeds defaults.
 */
class C404P_Integration_ActivationTest extends WP_UnitTestCase {

	/**
	 * Tear down: drop the logs table so DDL does not leak between tests.
	 *
	 * dbDelta issues DDL (CREATE TABLE), which implicitly commits any open
	 * transaction. The logs table must therefore be dropped manually rather
	 * than relying on WP_UnitTestCase's transaction rollback.
	 */
	public function tearDown(): void {
		global $wpdb;
		$helpers = new Helpers();
		$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . $helpers->table_logs ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		delete_option( Helpers::OPTION_KEY );
		parent::tearDown();
	}

	/**
	 * create_tables() should create the logs table.
	 */
	public function test_create_tables_creates_logs_table() {
		global $wpdb;
		ActivateClass::create_tables();
		$helpers = new Helpers();
		$wpdb->suppress_errors( true );
		$result = $wpdb->get_var( 'SELECT COUNT(*) FROM ' . $wpdb->prefix . $helpers->table_logs ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		$wpdb->suppress_errors( false );
		$this->assertNotNull( $result, 'Logs table should exist after create_tables().' );
	}

	/**
	 * Logs table should have the expected columns.
	 */
	public function test_logs_table_has_correct_columns() {
		global $wpdb;
		ActivateClass::create_tables();
		$helpers  = new Helpers();
		$columns  = $wpdb->get_col( 'SHOW COLUMNS FROM ' . $wpdb->prefix . $helpers->table_logs ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		$expected = array( 'id', 'ip', 'path', 'referer', 'user_agent', 'created', 'updated' );
		foreach ( $expected as $col ) {
			$this->assertContains( $col, $columns, "Logs table should have column '{$col}'." );
		}
	}

	/**
	 * initialize_options() should create the wp_options entry.
	 */
	public function test_initialize_options_creates_wp_options_entry() {
		ActivateClass::initialize_options();
		$stored = get_option( Helpers::OPTION_KEY );
		$this->assertIsArray( $stored, 'initialize_options() should create a wp_options entry.' );
	}

	/**
	 * initialize_options() should store all default setting keys.
	 */
	public function test_initialize_options_stores_all_default_keys() {
		ActivateClass::initialize_options();
		$helpers  = new Helpers();
		$stored   = get_option( Helpers::OPTION_KEY );
		$expected = array_keys( $helpers->defaults() );
		foreach ( $expected as $key ) {
			$this->assertArrayHasKey( $key, $stored, "Default key '{$key}' should be present after initialize_options()." );
		}
	}

	/**
	 * initialize_options() should not overwrite existing settings on re-activation.
	 */
	public function test_initialize_options_does_not_overwrite_existing_settings() {
		// Simulate a saved setting from a previous activation.
		update_option( Helpers::OPTION_KEY, array( 'mode' => 'url', 'mode_url' => 'https://example.com' ) );
		ActivateClass::initialize_options();
		$stored = get_option( Helpers::OPTION_KEY );
		$this->assertSame( 'url', $stored['mode'], 'Re-activation should not overwrite existing settings.' );
	}

	/**
	 * maybe_migrate_legacy_options() should be a no-op when no legacy table exists.
	 */
	public function test_maybe_migrate_legacy_options_is_noop_when_no_legacy_table() {
		ActivateClass::maybe_migrate_legacy_options();
		// No exception and no wp_options entry created means the no-op path ran.
		$this->assertFalse( get_option( Helpers::OPTION_KEY ), 'No wp_options entry should be created when there is nothing to migrate.' );
	}
}
