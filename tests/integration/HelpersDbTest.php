<?php
/**
 * Integration tests for the Helpers class database operations.
 *
 * @package Custom_404_Pro
 */

/**
 * Tests every public CRUD method in Helpers against a real MySQL database.
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
	 * Set up: create tables and a Helpers instance with admin privileges.
	 */
	public function setUp(): void {
		parent::setUp();
		$admin_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $admin_id );
		ActivateClass::create_tables();
		$this->helpers = new Helpers();
	}

	/**
	 * Tear down: drop both custom tables.
	 */
	public function tearDown(): void {
		global $wpdb;
		$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . $this->helpers->table_options ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . $this->helpers->table_logs ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		parent::tearDown();
	}

	// -------------------------------------------------------------------------
	// Options CRUD
	// -------------------------------------------------------------------------

	/**
	 * insert_option() should persist the value so get_option() returns it.
	 */
	public function test_insert_option_persists_value_in_database() {
		$this->helpers->insert_option( 'test_key', 'test_value' );
		$this->assertSame( 'test_value', $this->helpers->get_option( 'test_key' ) );
	}

	/**
	 * get_option() should return null for a key that was never inserted.
	 */
	public function test_get_option_returns_null_for_nonexistent_key() {
		$this->assertNull( $this->helpers->get_option( 'nonexistent_key' ) );
	}

	/**
	 * update_option() should change the stored value.
	 */
	public function test_update_option_modifies_existing_row() {
		$this->helpers->insert_option( 'fruit', 'apple' );
		$this->helpers->update_option( 'fruit', 'orange' );
		$this->assertSame( 'orange', $this->helpers->get_option( 'fruit' ) );
	}

	/**
	 * is_option() should return a row object for a key that exists.
	 */
	public function test_is_option_returns_row_object_for_existing_key() {
		$this->helpers->insert_option( 'exists', '1' );
		$row = $this->helpers->is_option( 'exists' );
		$this->assertIsObject( $row );
		$this->assertSame( 'exists', $row->name );
	}

	/**
	 * is_option() should return false for a key that does not exist.
	 */
	public function test_is_option_returns_false_for_missing_key() {
		$this->assertFalse( $this->helpers->is_option( 'missing_key' ) );
	}

	/**
	 * upsert_option() should insert a row when the option does not yet exist.
	 */
	public function test_upsert_option_inserts_when_option_does_not_exist() {
		$this->helpers->upsert_option( 'new_key', 'new_val' );
		$this->assertSame( 'new_val', $this->helpers->get_option( 'new_key' ) );
	}

	/**
	 * upsert_option() should update the value without creating a duplicate row.
	 */
	public function test_upsert_option_updates_and_does_not_duplicate() {
		global $wpdb;
		$this->helpers->insert_option( 'existing', 'first' );
		$this->helpers->upsert_option( 'existing', 'second' );
		$this->assertSame( 'second', $this->helpers->get_option( 'existing' ) );
		$count = (int) $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				'SELECT COUNT(*) FROM ' . $wpdb->prefix . $this->helpers->table_options . ' WHERE name = %s', // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				'existing'
			)
		);
		$this->assertSame( 1, $count, 'upsert_option() should not create duplicate rows.' );
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
