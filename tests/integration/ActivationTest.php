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
	 * Administrator user ID created in setUp.
	 *
	 * @var int
	 */
	private $admin_id;

	/**
	 * Set up: create an administrator user so capability checks pass.
	 */
	public function setUp(): void {
		parent::setUp();
		$this->admin_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $this->admin_id );
	}

	/**
	 * Tear down: drop both custom tables so DDL does not leak between tests.
	 *
	 * dbDelta issues DDL (CREATE TABLE), which implicitly commits the transaction
	 * that WP_UnitTestCase wraps around DML. The tables must be dropped manually.
	 */
	public function tearDown(): void {
		global $wpdb;
		$helpers = new Helpers();
		$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . $helpers->table_options ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . $helpers->table_logs ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		parent::tearDown();
	}

	/**
	 * create_tables() should create the options table.
	 */
	public function test_create_tables_creates_options_table() {
		global $wpdb;
		ActivateClass::create_tables();
		$helpers = new Helpers();
		$result  = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->prefix . $helpers->table_options ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$this->assertNotEmpty( $result, 'Options table should exist after create_tables().' );
	}

	/**
	 * create_tables() should create the logs table.
	 */
	public function test_create_tables_creates_logs_table() {
		global $wpdb;
		ActivateClass::create_tables();
		$helpers = new Helpers();
		$result  = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->prefix . $helpers->table_logs ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$this->assertNotEmpty( $result, 'Logs table should exist after create_tables().' );
	}

	/**
	 * Options table should have the expected columns.
	 */
	public function test_options_table_has_correct_columns() {
		global $wpdb;
		ActivateClass::create_tables();
		$helpers  = new Helpers();
		$columns  = $wpdb->get_col( 'SHOW COLUMNS FROM ' . $wpdb->prefix . $helpers->table_options ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		$expected = array( 'id', 'name', 'value', 'created', 'updated' );
		foreach ( $expected as $col ) {
			$this->assertContains( $col, $columns, "Options table should have column '{$col}'." );
		}
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
	 * initialize_options() should insert one row for each default option key.
	 */
	public function test_initialize_options_inserts_all_default_rows() {
		global $wpdb;
		ActivateClass::create_tables();
		ActivateClass::initialize_options();
		$helpers  = new Helpers();
		$count    = (int) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . $wpdb->prefix . $helpers->table_options ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		$expected = count( $helpers->options_defaults );
		$this->assertSame( $expected, $count, "initialize_options() should insert {$expected} rows." );
	}

	/**
	 * initialize_options() should insert rows for every expected option name.
	 */
	public function test_initialize_options_inserts_expected_option_names() {
		global $wpdb;
		ActivateClass::create_tables();
		ActivateClass::initialize_options();
		$helpers        = new Helpers();
		$inserted_names = $wpdb->get_col( 'SELECT name FROM ' . $wpdb->prefix . $helpers->table_options ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		$expected_names = array( 'mode', 'mode_page', 'mode_url', 'send_email', 'logging_enabled', 'redirect_error_code', 'log_ip' );
		foreach ( $expected_names as $name ) {
			$this->assertContains( $name, $inserted_names, "Default option '{$name}' should be present after initialize_options()." );
		}
	}
}
