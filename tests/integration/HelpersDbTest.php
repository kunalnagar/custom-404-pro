<?php
/**
 * Integration tests for the Helpers class database operations.
 *
 * @package Custom_404_Pro
 */

/**
 * Tests every public method in Helpers that touches the database.
 */
class C404P_Integration_HelpersDbTest extends WP_UnitTestCase {

	/**
	 * Fresh Helpers instance for each test.
	 *
	 * We use `new Helpers()` rather than `Helpers::singleton()` because the
	 * singleton stores its instance in a static local variable that cannot be
	 * reset between tests.
	 *
	 * @var Helpers
	 */
	private $helpers;

	/**
	 * Set up: create the logs table and a Helpers instance with admin privileges.
	 */
	public function setUp(): void {
		parent::setUp();
		$admin_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $admin_id );
		ActivateClass::create_tables();
		$this->helpers = new Helpers();
	}

	/**
	 * Tear down: drop the logs table.
	 */
	public function tearDown(): void {
		global $wpdb;
		$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . $this->helpers->table_logs ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		parent::tearDown();
	}

	// -------------------------------------------------------------------------
	// Settings via wp_options
	// -------------------------------------------------------------------------

	/**
	 * get_settings() should return defaults when no option is stored.
	 */
	public function test_get_settings_returns_defaults_when_no_option_stored() {
		$this->assertSame( $this->helpers->defaults(), $this->helpers->get_settings() );
	}

	/**
	 * update_settings() should persist values readable by get_settings().
	 */
	public function test_update_settings_persists_values() {
		$this->helpers->update_settings( array( 'mode' => 'url', 'mode_url' => 'https://example.com' ) );
		$settings = $this->helpers->get_settings();
		$this->assertSame( 'url', $settings['mode'] );
		$this->assertSame( 'https://example.com', $settings['mode_url'] );
	}

	/**
	 * update_settings() should merge with existing values rather than replace them.
	 */
	public function test_update_settings_merges_with_existing_values() {
		$this->helpers->update_settings( array( 'redirect_error_code' => 301 ) );
		$this->helpers->update_settings( array( 'mode' => 'url' ) );
		$this->assertSame( 301, (int) $this->helpers->get_setting( 'redirect_error_code' ) );
		$this->assertSame( 'url', $this->helpers->get_setting( 'mode' ) );
	}

	// -------------------------------------------------------------------------
	// Logs CRUD
	// -------------------------------------------------------------------------

	/**
	 * Helper to build a log stdClass.
	 *
	 * @param string $path Request path for the log entry.
	 * @return stdClass
	 */
	private function make_log( $path = '/missing-page' ) {
		$log             = new stdClass();
		$log->ip         = '127.0.0.1';
		$log->path       = $path;
		$log->referer    = 'https://example.com';
		$log->user_agent = 'PHPUnit';
		return $log;
	}

	/**
	 * create_logs() should insert a row into the logs table.
	 */
	public function test_create_logs_inserts_row_into_logs_table() {
		global $wpdb;
		$this->helpers->create_logs( array( $this->make_log() ), false );
		$count = (int) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . $wpdb->prefix . $this->helpers->table_logs ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		$this->assertSame( 1, $count );
	}

	/**
	 * get_logs() should return all inserted rows.
	 */
	public function test_get_logs_returns_all_inserted_rows() {
		$this->helpers->create_logs( array( $this->make_log( '/page-one' ), $this->make_log( '/page-two' ) ), false );
		$logs = $this->helpers->get_logs();
		$this->assertIsArray( $logs );
		$this->assertCount( 2, $logs );
	}

	/**
	 * delete_logs('all') should truncate the logs table.
	 */
	public function test_delete_logs_with_all_truncates_table() {
		global $wpdb;
		$this->helpers->create_logs( array( $this->make_log(), $this->make_log() ), false );
		$this->helpers->delete_logs( 'all' );
		$count = (int) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . $wpdb->prefix . $this->helpers->table_logs ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		$this->assertSame( 0, $count );
	}

	/**
	 * delete_logs( $id ) should remove only the specified row.
	 */
	public function test_delete_logs_with_single_id_removes_only_that_row() {
		global $wpdb;
		$this->helpers->create_logs( array( $this->make_log( '/first' ), $this->make_log( '/second' ) ), false );
		$first_id = (int) $wpdb->get_var( 'SELECT id FROM ' . $wpdb->prefix . $this->helpers->table_logs . ' ORDER BY id ASC LIMIT 1' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		$this->helpers->delete_logs( $first_id );
		$count = (int) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . $wpdb->prefix . $this->helpers->table_logs ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		$this->assertSame( 1, $count );
	}

	/**
	 * delete_logs( $ids_array ) should remove all specified rows.
	 */
	public function test_delete_logs_with_array_of_ids_removes_specified_rows() {
		global $wpdb;
		$this->helpers->create_logs(
			array(
				$this->make_log( '/a' ),
				$this->make_log( '/b' ),
				$this->make_log( '/c' ),
			),
			false
		);
		$ids = $wpdb->get_col( 'SELECT id FROM ' . $wpdb->prefix . $this->helpers->table_logs . ' ORDER BY id ASC LIMIT 2' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		$this->helpers->delete_logs( array_map( 'intval', $ids ) );
		$count = (int) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . $wpdb->prefix . $this->helpers->table_logs ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		$this->assertSame( 1, $count );
	}
}
