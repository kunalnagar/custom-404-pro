<?php
/**
 * Integration tests for the LogsClass list table.
 *
 * @package Custom_404_Pro
 */

/**
 * Tests LogsClass query building: search, sorting, and pagination.
 *
 * These run against a real MySQL database so that malformed SQL surfaces as an
 * actual query error rather than passing silently against a stub.
 */
class C404P_Integration_LogsTableTest extends WP_UnitTestCase {

	/**
	 * Helpers instance used for table setup and assertions.
	 *
	 * @var Helpers
	 */
	private $helpers;

	/**
	 * Set up: create the logs table and seed a known set of rows.
	 */
	public function setUp(): void {
		parent::setUp();

		$admin_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $admin_id );

		ActivateClass::create_tables();
		$this->helpers = new Helpers();

		// Surface any MySQL error as a test failure instead of an empty result set.
		$GLOBALS['wpdb']->suppress_errors( false );
		$GLOBALS['wpdb']->show_errors( false );

		$_GET     = array();
		$_REQUEST = array();
	}

	/**
	 * Tear down: reset superglobals and drop the logs table.
	 */
	public function tearDown(): void {
		global $wpdb;

		$_GET     = array();
		$_REQUEST = array();

		$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . $this->helpers->table_logs ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared

		parent::tearDown();
	}

	/**
	 * Inserts a log row with explicit field values.
	 *
	 * @param string $ip         IP address.
	 * @param string $path       Request path.
	 * @param string $referer    Referer URL.
	 * @param string $user_agent User agent string.
	 */
	private function insert_log( string $ip, string $path, string $referer = '', string $user_agent = 'PHPUnit' ) {
		global $wpdb;
		$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$wpdb->prefix . $this->helpers->table_logs,
			array(
				'ip'         => $ip,
				'path'       => $path,
				'referer'    => $referer,
				'user_agent' => $user_agent,
			)
		);
	}

	/**
	 * Inserts $count generic log rows.
	 *
	 * @param int $count Number of rows to insert.
	 */
	private function insert_logs( int $count ) {
		for ( $i = 0; $i < $count; $i++ ) {
			$this->insert_log( '10.0.0.' . ( $i % 250 ), '/missing-' . $i );
		}
	}

	/**
	 * Asserts that the last query executed did not produce a MySQL error.
	 *
	 * @param string $message Assertion message.
	 */
	private function assertNoDbError( string $message ) { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid
		global $wpdb;
		$this->assertSame( '', (string) $wpdb->last_error, $message . ' MySQL error: ' . $wpdb->last_error );
	}

	// -------------------------------------------------------------------------
	// Sorting
	// -------------------------------------------------------------------------

	/**
	 * Every column advertised by get_sortable_columns() must actually sort.
	 *
	 * Regression: get_sortable_columns() returned orderby keys ('ip', 'path',
	 * 'referer', 'user_agent') that manage_sorting() did not recognise, so the
	 * bare sort direction was appended with no ORDER BY clause, producing
	 * "SELECT * FROM wp_custom_404_pro_logs ASC" — a MySQL syntax error.
	 *
	 * @dataProvider sortable_column_provider
	 * @param string $orderby The orderby query argument.
	 */
	public function test_every_sortable_column_produces_valid_sql( string $orderby ) {
		$this->insert_logs( 3 );

		$_GET['orderby'] = $orderby;
		$_GET['order']   = 'asc';

		$table = new LogsClass();
		$table->prepare_items();

		$this->assertNoDbError( "Sorting by '{$orderby}' produced invalid SQL." );
		$this->assertCount( 3, $table->items, "Sorting by '{$orderby}' returned no rows." );
	}

	/**
	 * Supplies every orderby key advertised as sortable.
	 *
	 * @return array<string, array<string>>
	 */
	public function sortable_column_provider(): array {
		$cases = array();
		foreach ( array_keys( ( new LogsClass() )->get_sortable_columns() ) as $key ) {
			$cases[ $key ] = array( $key );
		}
		return $cases;
	}

	/**
	 * Sorting descending should reverse the ordering.
	 */
	public function test_sorting_respects_descending_direction() {
		$this->insert_log( '10.0.0.1', '/aaa' );
		$this->insert_log( '10.0.0.2', '/bbb' );
		$this->insert_log( '10.0.0.3', '/ccc' );

		$_GET['orderby'] = 'path';
		$_GET['order']   = 'desc';

		$table = new LogsClass();
		$table->prepare_items();

		$this->assertNoDbError( 'Descending sort produced invalid SQL.' );
		$this->assertSame( '/ccc', $table->items[0]['path'] );
		$this->assertSame( '/aaa', $table->items[2]['path'] );
	}

	/**
	 * An unknown orderby value must not reach the query as raw SQL.
	 */
	public function test_unknown_orderby_is_ignored_and_does_not_break_the_query() {
		$this->insert_logs( 3 );

		$_GET['orderby'] = 'id; DROP TABLE wp_posts';
		$_GET['order']   = 'asc';

		$table = new LogsClass();
		$table->prepare_items();

		$this->assertNoDbError( 'Unknown orderby produced invalid SQL.' );
		$this->assertCount( 3, $table->items, 'Unknown orderby should fall back to an unsorted result set.' );
	}

	/**
	 * An unknown order direction must fall back to ASC rather than be injected.
	 */
	public function test_unknown_order_direction_falls_back_to_ascending() {
		$this->insert_log( '10.0.0.1', '/aaa' );
		$this->insert_log( '10.0.0.2', '/bbb' );

		$_GET['orderby'] = 'path';
		$_GET['order']   = 'sideways; DROP TABLE wp_posts';

		$table = new LogsClass();
		$table->prepare_items();

		$this->assertNoDbError( 'Unknown order direction produced invalid SQL.' );
		$this->assertSame( '/aaa', $table->items[0]['path'] );
	}

	// -------------------------------------------------------------------------
	// Search
	// -------------------------------------------------------------------------

	/**
	 * Search should restrict results to matching rows.
	 */
	public function test_search_filters_results() {
		$this->insert_log( '10.0.0.1', '/wp-admin-login' );
		$this->insert_log( '10.0.0.2', '/some-other-page' );

		$_GET['s'] = 'admin-login';

		$table = new LogsClass();
		$table->prepare_items();

		$this->assertNoDbError( 'Search produced invalid SQL.' );
		$this->assertCount( 1, $table->items );
		$this->assertSame( '/wp-admin-login', $table->items[0]['path'] );
	}

	/**
	 * Search combined with sorting must produce a valid query.
	 *
	 * Regression: prepare_items() appended ORDER BY before WHERE, yielding
	 * "SELECT * FROM t ORDER BY path ASC WHERE (...)" — a MySQL syntax error.
	 * Every search-then-sort interaction on the Logs screen hit this.
	 */
	public function test_search_combined_with_sorting_produces_valid_sql() {
		$this->insert_log( '10.0.0.1', '/broken-aaa' );
		$this->insert_log( '10.0.0.2', '/broken-bbb' );
		$this->insert_log( '10.0.0.3', '/unrelated' );

		$_GET['s']       = 'broken';
		$_GET['orderby'] = 'path';
		$_GET['order']   = 'desc';

		$table = new LogsClass();
		$table->prepare_items();

		$this->assertNoDbError( 'Search combined with sorting produced invalid SQL.' );
		$this->assertCount( 2, $table->items, 'Search + sort should return only the matching rows.' );
		$this->assertSame( '/broken-bbb', $table->items[0]['path'], 'Search + sort should apply the sort order.' );
	}

	/**
	 * A search term containing SQL wildcards must be treated as a literal.
	 */
	public function test_search_escapes_like_wildcards() {
		$this->insert_log( '10.0.0.1', '/100%-off' );
		$this->insert_log( '10.0.0.2', '/unrelated' );

		$_GET['s'] = '100%-off';

		$table = new LogsClass();
		$table->prepare_items();

		$this->assertNoDbError( 'Wildcard search produced invalid SQL.' );
		$this->assertCount( 1, $table->items );
	}

	// -------------------------------------------------------------------------
	// Pagination
	// -------------------------------------------------------------------------

	/**
	 * Pagination must be applied in SQL, not by slicing a full table read.
	 *
	 * Regression: prepare_items() ran "SELECT *" with no LIMIT and then
	 * array_slice()'d in PHP, so rendering page 1 of a million-row log table
	 * pulled every row into memory.
	 */
	public function test_pagination_limits_rows_read_from_the_database() {
		$this->insert_logs( 120 );

		$table = new LogsClass();
		$table->prepare_items();

		$this->assertNoDbError( 'Paginated query produced invalid SQL.' );
		$this->assertCount( 50, $table->items, 'Page 1 should contain exactly one page of rows.' );

		$last_query = $GLOBALS['wpdb']->last_query;
		$this->assertMatchesRegularExpression(
			'/LIMIT\s+\d+/i',
			$last_query,
			'The row query must apply LIMIT in SQL rather than slicing in PHP.'
		);
	}

	/**
	 * The pagination total must reflect every matching row, not just the page.
	 */
	public function test_pagination_total_counts_all_matching_rows() {
		$this->insert_logs( 120 );

		$table = new LogsClass();
		$table->prepare_items();

		$this->assertSame( 120, (int) $table->get_pagination_arg( 'total_items' ) );
		$this->assertSame( 3, (int) $table->get_pagination_arg( 'total_pages' ) );
	}

	/**
	 * The pagination total must respect an active search filter.
	 */
	public function test_pagination_total_respects_active_search() {
		$this->insert_logs( 60 );
		$this->insert_log( '10.0.0.1', '/uniquely-broken' );

		$_GET['s'] = 'uniquely-broken';

		$table = new LogsClass();
		$table->prepare_items();

		$this->assertNoDbError( 'Counting with an active search produced invalid SQL.' );
		$this->assertSame( 1, (int) $table->get_pagination_arg( 'total_items' ) );
		$this->assertCount( 1, $table->items );
	}

	/**
	 * Requesting page 2 should return the next slice of rows, not the first.
	 */
	public function test_second_page_returns_different_rows_than_first_page() {
		$this->insert_logs( 120 );

		$_GET['orderby'] = 'created';
		$_GET['order']   = 'asc';

		$_REQUEST['paged'] = 1;
		$_GET['paged']     = 1;
		$first             = new LogsClass();
		$first->prepare_items();

		$_REQUEST['paged'] = 2;
		$_GET['paged']     = 2;
		$second            = new LogsClass();
		$second->prepare_items();

		$this->assertNoDbError( 'Page 2 query produced invalid SQL.' );
		$this->assertCount( 50, $second->items, 'Page 2 should be a full page.' );
		$this->assertNotSame(
			wp_list_pluck( $first->items, 'id' ),
			wp_list_pluck( $second->items, 'id' ),
			'Page 2 must return a different slice of rows than page 1.'
		);
	}

	// -------------------------------------------------------------------------
	// Pagination stability
	// -------------------------------------------------------------------------

	/**
	 * Paging a sort whose values tie must not repeat or skip rows.
	 *
	 * `created` has one-second resolution, and a bot crawl produces dozens of
	 * 404s inside the same second. Sorting on a column with duplicate values
	 * under LIMIT/OFFSET has no defined row order in SQL unless the sort ends in
	 * something unique, so page 2 could repeat rows already shown on page 1 and
	 * silently omit others entirely.
	 */
	public function test_paging_a_tied_sort_does_not_repeat_or_skip_rows() {
		global $wpdb;

		// 120 rows sharing one timestamp, as a burst of 404s would produce.
		$values = array();
		for ( $i = 0; $i < 120; $i++ ) {
			$values[] = $wpdb->prepare( '(%s, %s, %s, %s, %s)', '10.0.0.1', '/burst-' . $i, '', 'crawler', '2026-01-01 12:00:00' );
		}
		$wpdb->query( 'INSERT INTO ' . $wpdb->prefix . $this->helpers->table_logs . ' (ip, path, referer, user_agent, created) VALUES ' . implode( ',', $values ) ); // phpcs:ignore

		$_GET['orderby'] = 'created';
		$_GET['order']   = 'desc';

		$seen = array();
		foreach ( array( 1, 2, 3 ) as $page ) {
			$_GET['paged']     = $page;
			$_REQUEST['paged'] = $page;
			$table             = new LogsClass();
			$table->prepare_items();
			$seen = array_merge( $seen, wp_list_pluck( $table->items, 'id' ) );
		}

		$this->assertNoDbError( 'Paging a tied sort produced invalid SQL.' );
		$this->assertCount( 120, $seen, 'Paging must return every row exactly once across all pages.' );
		$this->assertSame( count( $seen ), count( array_unique( $seen ) ), 'No row may appear on more than one page.' );
	}

	/**
	 * The unsorted default must also page deterministically.
	 */
	public function test_default_unsorted_paging_does_not_repeat_or_skip_rows() {
		$this->insert_logs( 120 );

		$seen = array();
		foreach ( array( 1, 2, 3 ) as $page ) {
			$_GET['paged']     = $page;
			$_REQUEST['paged'] = $page;
			$table             = new LogsClass();
			$table->prepare_items();
			$seen = array_merge( $seen, wp_list_pluck( $table->items, 'id' ) );
		}

		$this->assertCount( 120, $seen, 'Default paging must return every row exactly once.' );
		$this->assertSame( count( $seen ), count( array_unique( $seen ) ), 'No row may appear on more than one page.' );
	}

	/**
	 * The row query must always carry an ORDER BY.
	 *
	 * LIMIT/OFFSET without one has no defined row order at all.
	 */
	public function test_row_query_always_has_an_order_by() {
		$this->insert_logs( 10 );

		$table = new LogsClass();
		$table->prepare_items();

		$this->assertMatchesRegularExpression(
			'/ORDER BY/i',
			$GLOBALS['wpdb']->last_query,
			'The paginated row query must always specify an ORDER BY.'
		);
	}
}
