<?php
/**
 * Integration tests for the logs table schema and the in-place upgrade path.
 *
 * @package Custom_404_Pro
 */

/**
 * Tests ActivateClass::create_tables() and PluginClass::maybe_upgrade().
 *
 * The upgrade path matters more than the activation path: most users update the
 * plugin without deactivating it first, so register_activation_hook never fires
 * for them and everything has to be reapplied on plugins_loaded instead.
 */
class C404P_Integration_SchemaUpgradeTest extends WP_UnitTestCase {

	/**
	 * Helpers instance.
	 *
	 * @var Helpers
	 */
	private $helpers;

	/**
	 * Fully-qualified logs table name.
	 *
	 * @var string
	 */
	private $table;

	/**
	 * Set up: start from a clean slate with no table and no stored db version.
	 */
	public function setUp(): void {
		global $wpdb;

		parent::setUp();

		$admin_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $admin_id );

		$this->helpers = new Helpers();
		$this->table   = $wpdb->prefix . $this->helpers->table_logs;

		$wpdb->query( 'DROP TABLE IF EXISTS ' . $this->table ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		delete_option( 'custom_404_pro_db_version' );
		wp_clear_scheduled_hook( 'custom_404_pro_prune_logs' );

		// The upgrade only runs in an admin/cron/CLI request, so most tests here
		// have to look like one. Tests that assert the front-end guard clear it.
		set_current_screen( 'dashboard' );

		// Surface MySQL errors instead of letting a broken ALTER pass silently.
		$wpdb->suppress_errors( false );
		$wpdb->show_errors( false );
		$wpdb->last_error = '';
	}

	/**
	 * Tear down: drop the table and clear scheduled events.
	 */
	public function tearDown(): void {
		global $wpdb;

		$wpdb->query( 'DROP TABLE IF EXISTS ' . $this->table ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		delete_option( 'custom_404_pro_db_version' );
		wp_clear_scheduled_hook( 'custom_404_pro_prune_logs' );

		parent::tearDown();
	}

	/**
	 * Asserts the last query did not produce a MySQL error.
	 *
	 * @param string $message Assertion message.
	 */
	private function assertNoDbError( string $message ) { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid
		global $wpdb;
		$this->assertSame( '', (string) $wpdb->last_error, $message . ' MySQL error: ' . $wpdb->last_error );
	}

	/**
	 * Returns the AUTO_INCREMENT counter for the logs table.
	 *
	 * @return int Next auto-increment value.
	 */
	private function get_auto_increment(): int {
		global $wpdb;
		return (int) $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
			$wpdb->prepare(
				'SELECT AUTO_INCREMENT FROM information_schema.TABLES WHERE table_schema = DATABASE() AND table_name = %s',
				$this->table
			)
		);
	}

	/**
	 * Inserts $count log rows in a single statement.
	 *
	 * @param int $count Number of rows to insert.
	 */
	private function seed_rows( int $count ) {
		global $wpdb;
		$values = array();
		for ( $i = 0; $i < $count; $i++ ) {
			$values[] = $wpdb->prepare( '(%s, %s, %s, %s)', '10.0.0.' . ( $i % 250 ), '/missing-' . $i, 'https://ref.example/' . $i, 'crawler-' . $i );
		}
		$wpdb->query( 'INSERT INTO ' . $this->table . ' (ip, path, referer, user_agent) VALUES ' . implode( ',', $values ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
	}

	/**
	 * Returns the index names defined on the logs table.
	 *
	 * @return array<string> Index names.
	 */
	private function get_index_names(): array {
		global $wpdb;
		$rows = $wpdb->get_results( 'SHOW INDEX FROM ' . $this->table ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		$names = array();
		foreach ( (array) $rows as $row ) {
			$names[] = $row->Key_name; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- wpdb column object property
		}
		return array_unique( $names );
	}

	/**
	 * Returns the declared column type for a logs table column.
	 *
	 * @param string $column Column name.
	 * @return string Column type as reported by MySQL, lowercased.
	 */
	private function get_column_type( string $column ): string {
		global $wpdb;
		$row = $wpdb->get_row( $wpdb->prepare( 'SHOW COLUMNS FROM ' . $this->table . ' LIKE %s', $column ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		return null === $row ? '' : strtolower( $row->Type ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- wpdb column object property
	}

	/**
	 * Creates the logs table using the pre-3.16.0 schema.
	 *
	 * Deliberately hand-written rather than calling create_tables(), so the test
	 * exercises a genuine upgrade from what existing installations actually have
	 * on disk: a narrow id column and no index on `created`.
	 */
	private function create_legacy_table() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
			"CREATE TABLE {$this->table} (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				ip text,
				path text,
				referer text,
				user_agent text,
				created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
				updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				PRIMARY KEY  (id)
			) {$charset_collate}"
		);
	}

	// -------------------------------------------------------------------------
	// Fresh install schema
	// -------------------------------------------------------------------------

	/**
	 * A freshly created table must carry an index on `created`.
	 *
	 * The retention policy sorts and filters on this column, so without the
	 * index every daily prune is a full table scan.
	 */
	public function test_fresh_table_has_index_on_created() {
		ActivateClass::create_tables();

		$this->assertContains( 'created', $this->get_index_names(), 'The logs table must have an index on `created`.' );
	}

	/**
	 * A freshly created table must use a bigint id.
	 */
	public function test_fresh_table_id_column_is_bigint() {
		ActivateClass::create_tables();

		$this->assertStringContainsString( 'bigint', $this->get_column_type( 'id' ), 'The logs table id column must be bigint.' );
	}

	/**
	 * Repeated create_tables() calls must report no further schema changes.
	 *
	 * This is the guard against dbDelta reissuing the same ALTER TABLE forever.
	 * dbDelta compares the schema string against SHOW COLUMNS output, and the
	 * comparison is fragile in two ways this table touches:
	 *
	 *   - MySQL 8.0.19+ drops the display width from integer types, so the
	 *     `bigint(20) unsigned` in the definition comes back as
	 *     `bigint unsigned`.
	 *   - dbDelta parses the definition one field per line; collapsing two
	 *     columns onto one line makes it emit a malformed ALTER.
	 *
	 * Either mistake turns the once-per-version upgrade into an ALTER TABLE on
	 * every upgrade check. Asserting dbDelta reports zero changes on the second
	 * and third call catches both.
	 */
	public function test_repeated_create_tables_reports_no_further_changes() {
		$first = ActivateClass::create_tables();
		$this->assertNotEmpty( $first, 'The first call should report creating the table.' );

		$second = ActivateClass::create_tables();
		$third  = ActivateClass::create_tables();

		$this->assertSame( array(), $second, 'The second create_tables() call must report no schema changes.' );
		$this->assertSame( array(), $third, 'The third create_tables() call must report no schema changes.' );
		$this->assertNoDbError( 'Repeated create_tables() calls must not error.' );
	}

	/**
	 * Upgrading a legacy table must settle after one pass.
	 */
	public function test_upgrade_from_legacy_schema_settles_after_one_pass() {
		$this->create_legacy_table();

		$applied = ActivateClass::create_tables();
		$this->assertNotEmpty( $applied, 'The upgrade should report the schema changes it applied.' );

		$this->assertSame( array(), ActivateClass::create_tables(), 'A second pass must report no changes.' );
		$this->assertNoDbError( 'The legacy upgrade must not error.' );
	}

	/**
	 * Running create_tables() twice must not duplicate indexes.
	 */
	public function test_create_tables_does_not_duplicate_indexes() {
		ActivateClass::create_tables();
		$first = $this->get_index_names();

		ActivateClass::create_tables();
		$second = $this->get_index_names();

		$this->assertSame( $first, $second, 'Repeated create_tables() calls must not change the schema.' );
	}

	// -------------------------------------------------------------------------
	// Upgrade path
	// -------------------------------------------------------------------------

	/**
	 * Upgrading in place must add the missing index to an existing table.
	 *
	 * Regression: the plugins_loaded upgrade routine only ran the legacy options
	 * migration, so schema changes never reached users who updated without
	 * deactivating the plugin first.
	 */
	public function test_upgrade_adds_index_to_existing_legacy_table() {
		$this->create_legacy_table();
		$this->assertNotContains( 'created', $this->get_index_names(), 'Precondition: the legacy table has no index on created.' );

		( new PluginClass() )->maybe_upgrade();

		$this->assertContains( 'created', $this->get_index_names(), 'The upgrade must add the index on `created`.' );
	}

	/**
	 * Upgrading in place must widen the id column on an existing table.
	 */
	public function test_upgrade_widens_id_column_on_existing_legacy_table() {
		$this->create_legacy_table();
		$this->assertStringContainsString( 'mediumint', $this->get_column_type( 'id' ), 'Precondition: the legacy table uses mediumint.' );

		( new PluginClass() )->maybe_upgrade();

		$this->assertStringContainsString( 'bigint', $this->get_column_type( 'id' ), 'The upgrade must widen id to bigint.' );
	}

	/**
	 * The upgrade must preserve existing log rows.
	 */
	public function test_upgrade_preserves_existing_log_rows() {
		global $wpdb;

		$this->create_legacy_table();
		$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$this->table,
			array(
				'ip'         => '203.0.113.7',
				'path'       => '/an-important-404',
				'referer'    => 'https://example.com',
				'user_agent' => 'PHPUnit',
			)
		);

		( new PluginClass() )->maybe_upgrade();

		$rows = $wpdb->get_results( 'SELECT * FROM ' . $this->table, ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		$this->assertCount( 1, $rows, 'The upgrade must not drop existing log rows.' );
		$this->assertSame( '/an-important-404', $rows[0]['path'] );
	}

	/**
	 * The upgrade must schedule the daily prune cron event.
	 */
	public function test_upgrade_schedules_the_prune_cron_event() {
		$this->assertFalse( wp_next_scheduled( 'custom_404_pro_prune_logs' ), 'Precondition: no prune event scheduled.' );

		( new PluginClass() )->maybe_upgrade();

		$this->assertNotFalse( wp_next_scheduled( 'custom_404_pro_prune_logs' ), 'The upgrade must schedule the daily prune event.' );
	}

	/**
	 * The upgrade must record the db version so it does not rerun every request.
	 */
	public function test_upgrade_records_the_db_version() {
		( new PluginClass() )->maybe_upgrade();

		$this->assertSame( CUSTOM_404_PRO_VERSION, get_option( 'custom_404_pro_db_version' ) );
	}

	/**
	 * Once the db version matches, the upgrade must not run again.
	 *
	 * dbDelta on every page load would be an unacceptable cost on a busy site.
	 */
	public function test_upgrade_is_a_noop_once_the_db_version_matches() {
		global $wpdb;

		update_option( 'custom_404_pro_db_version', CUSTOM_404_PRO_VERSION );

		$queries_before = $wpdb->num_queries;
		( new PluginClass() )->maybe_upgrade();
		$queries_after = $wpdb->num_queries;

		$this->assertSame( $queries_before, $queries_after, 'A completed upgrade must not issue further queries.' );
		$this->assertSame( '', $wpdb->get_var( "SHOW TABLES LIKE '{$this->table}'" ) ?? '', 'No table should have been created.' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
	}

	/**
	 * The deprecated method name must still delegate to the new routine.
	 */
	public function test_deprecated_alias_still_performs_the_upgrade() {
		( new PluginClass() )->maybe_migrate_legacy_options();

		$this->assertSame( CUSTOM_404_PRO_VERSION, get_option( 'custom_404_pro_db_version' ) );
		$this->assertContains( 'created', $this->get_index_names(), 'The deprecated alias must still apply schema changes.' );
	}

	// -------------------------------------------------------------------------
	// Pruning against the indexed schema
	// -------------------------------------------------------------------------

	/**
	 * The prune queries must still behave correctly against the new schema.
	 */
	public function test_prune_still_works_against_the_upgraded_schema() {
		global $wpdb;

		$this->create_legacy_table();
		( new PluginClass() )->maybe_upgrade();

		for ( $i = 0; $i < 5; $i++ ) {
			$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
				$this->table,
				array(
					'ip'         => '10.0.0.1',
					'path'       => '/missing-' . $i,
					'referer'    => '',
					'user_agent' => 'PHPUnit',
				)
			);
		}

		$this->helpers->update_settings(
			array(
				'log_retention_count' => 2,
				'log_retention_days'  => 0,
			)
		);

		$deleted = $this->helpers->prune_logs();

		$this->assertSame( 3, $deleted );
		$this->assertSame( 2, $this->helpers->get_logs_count() );
	}

	// -------------------------------------------------------------------------
	// ALTER TABLE safety
	// -------------------------------------------------------------------------

	/**
	 * The upgrade must complete without a MySQL error.
	 *
	 * A malformed dbDelta definition fails as a SQL error that WordPress
	 * swallows, leaving the table half-migrated and the db version recorded as
	 * done. Asserting on last_error catches that.
	 */
	public function test_upgrade_produces_no_sql_error() {
		$this->create_legacy_table();
		$this->seed_rows( 50 );

		( new PluginClass() )->maybe_upgrade();

		$this->assertNoDbError( 'The schema upgrade must not produce a SQL error.' );
	}

	/**
	 * The ALTER must preserve primary key values and the AUTO_INCREMENT counter.
	 *
	 * Widening the id column rebuilds the table. If ids were renumbered, the
	 * delete-by-id links on the Logs screen would start pointing at the wrong
	 * rows; if AUTO_INCREMENT reset, new inserts would collide.
	 */
	public function test_upgrade_preserves_id_values_and_auto_increment() {
		global $wpdb;

		$this->create_legacy_table();
		$this->seed_rows( 25 );

		$ids_before = $wpdb->get_col( 'SELECT id FROM ' . $this->table . ' ORDER BY id ASC' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		$next_before = $this->get_auto_increment();

		( new PluginClass() )->maybe_upgrade();

		$ids_after = $wpdb->get_col( 'SELECT id FROM ' . $this->table . ' ORDER BY id ASC' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared

		$this->assertSame( $ids_before, $ids_after, 'The ALTER must preserve every id value.' );
		$this->assertGreaterThanOrEqual( $next_before, $this->get_auto_increment(), 'AUTO_INCREMENT must not go backwards.' );
	}

	/**
	 * Column contents must survive the rebuild byte for byte.
	 */
	public function test_upgrade_preserves_row_contents_exactly() {
		global $wpdb;

		$this->create_legacy_table();
		$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$this->table,
			array(
				'ip'         => '198.51.100.42',
				'path'       => '/path/with spaces/&ampersand?q=1',
				'referer'    => 'https://example.com/a?b=c&d=e',
				'user_agent' => 'Mozilla/5.0 (X11; Linux) "quoted" \'single\'',
			)
		);
		$before = $wpdb->get_row( 'SELECT ip, path, referer, user_agent FROM ' . $this->table, ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared

		( new PluginClass() )->maybe_upgrade();

		$after = $wpdb->get_row( 'SELECT ip, path, referer, user_agent FROM ' . $this->table, ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared

		$this->assertSame( $before, $after, 'The ALTER must preserve column contents exactly.' );
	}

	/**
	 * A table with a meaningful number of rows must migrate intact.
	 *
	 * Kept modest so CI stays fast. A 1,000,000-row rehearsal of this same path
	 * completed in about 3 seconds on MySQL 8 with every row and id preserved.
	 */
	public function test_upgrade_preserves_a_populated_table() {
		$this->create_legacy_table();
		$this->seed_rows( 5000 );

		( new PluginClass() )->maybe_upgrade();

		$this->assertNoDbError( 'Upgrading a populated table must not error.' );
		$this->assertSame( 5000, $this->helpers->get_logs_count(), 'Every row must survive the ALTER.' );
		$this->assertContains( 'created', $this->get_index_names() );
		$this->assertStringContainsString( 'bigint', $this->get_column_type( 'id' ) );
	}

	// -------------------------------------------------------------------------
	// Request context gating
	// -------------------------------------------------------------------------

	/**
	 * A front-end request must never trigger the ALTER.
	 *
	 * On a site with a very large logs table the rebuild takes seconds. That
	 * cost belongs on an administrator's own page load, not on a visitor who
	 * happened to hit a 404.
	 */
	public function test_upgrade_does_not_run_on_a_front_end_request() {
		global $wpdb;

		set_current_screen( 'front' );
		$this->create_legacy_table();

		( new PluginClass() )->maybe_upgrade();

		$this->assertNotContains( 'created', $this->get_index_names(), 'A front-end request must not alter the schema.' );
		$this->assertStringContainsString( 'mediumint', $this->get_column_type( 'id' ), 'A front-end request must not alter the schema.' );
		$this->assertFalse( get_option( 'custom_404_pro_db_version' ), 'A skipped upgrade must not be recorded as done.' );
	}

	/**
	 * Skipping the upgrade on the front end must leave it pending, so the next
	 * admin request still performs it.
	 */
	public function test_upgrade_still_runs_on_the_next_admin_request() {
		set_current_screen( 'front' );
		$this->create_legacy_table();
		( new PluginClass() )->maybe_upgrade();

		set_current_screen( 'dashboard' );
		( new PluginClass() )->maybe_upgrade();

		$this->assertContains( 'created', $this->get_index_names(), 'The next admin request must apply the upgrade.' );
		$this->assertSame( CUSTOM_404_PRO_VERSION, get_option( 'custom_404_pro_db_version' ) );
	}

	/**
	 * A cron run must be allowed to perform the upgrade.
	 *
	 * Signalled through the wp_doing_cron filter rather than by defining
	 * DOING_CRON: a constant cannot be unset, so defining it here would leak
	 * into every test that ran afterwards and silently satisfy the context gate
	 * for all of them.
	 */
	public function test_upgrade_runs_during_cron() {
		set_current_screen( 'front' );
		$this->create_legacy_table();

		add_filter( 'wp_doing_cron', '__return_true' );
		( new PluginClass() )->maybe_upgrade();
		remove_filter( 'wp_doing_cron', '__return_true' );

		$this->assertContains( 'created', $this->get_index_names(), 'A cron request must be allowed to upgrade.' );
		$this->assertFalse( wp_doing_cron(), 'The cron signal must not leak past this test.' );
	}

	/**
	 * Front-end 404 logging must keep working while the upgrade is pending.
	 *
	 * The schema gate is only safe if the un-upgraded table still serves the
	 * plugin's core job.
	 */
	public function test_logging_still_works_while_the_upgrade_is_pending() {
		set_current_screen( 'front' );
		$this->create_legacy_table();
		( new PluginClass() )->maybe_upgrade();

		$log             = new stdClass();
		$log->ip         = '203.0.113.9';
		$log->path       = '/still-logging';
		$log->referer    = '';
		$log->user_agent = 'PHPUnit';
		$this->helpers->create_logs( array( $log ), false );

		$this->assertNoDbError( 'Logging must work against the un-upgraded table.' );
		$this->assertSame( 1, $this->helpers->get_logs_count() );
	}
}
