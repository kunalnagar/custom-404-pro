<?php
/**
 * Logs list table class.
 *
 * @package Custom_404_Pro
 */

if ( ! class_exists( 'WP_List_Table' ) ) {
	include_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Logs class.
 */
class LogsClass extends WP_List_Table {

	/**
	 * Number of log rows shown per page.
	 *
	 * @since 3.15.4
	 * @var int
	 */
	const PER_PAGE = 50;

	/**
	 * Maps an `orderby` query argument to a real database column.
	 *
	 * Both the current column keys and the short legacy keys ('i', 'p', 'r', 'u')
	 * are accepted so that bookmarked sort URLs from older versions keep working.
	 * Any value outside this map is discarded rather than interpolated into SQL.
	 *
	 * @since 3.15.4
	 * @var array<string, string>
	 */
	const SORTABLE_COLUMN_MAP = array(
		'created'    => 'created',
		'ip'         => 'ip',
		'path'       => 'path',
		'referer'    => 'referer',
		'user_agent' => 'user_agent',
		'i'          => 'ip',
		'p'          => 'path',
		'r'          => 'referer',
		'u'          => 'user_agent',
	);

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => 'log',
				'plural'   => 'logs',
				'ajax'     => false,
			)
		);
	}

	/**
	 * Add stuff on top or bottom of the Logs Table.
	 *
	 * @param string $which top/bottom.
	 * @return void
	 */
	public function extra_tablenav( $which ) {
		// No additional navigation content needed.
	}

	/**
	 * Prepares the list of items for displaying.
	 *
	 * The query is assembled in SQL order — WHERE, then ORDER BY, then LIMIT —
	 * and pagination is applied by the database. Only the rows for the current
	 * page are ever read into PHP, so the screen stays usable on sites with very
	 * large log tables.
	 *
	 * @since 3.15.4 Pagination moved into SQL; clause order corrected.
	 */
	public function prepare_items() {
		global $wpdb;

		$columns               = $this->get_columns();
		$hidden                = array();
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );

		$helpers = Helpers::singleton();
		$table   = $wpdb->prefix . $helpers->table_logs;

		// WHERE must be built first: appending ORDER BY before it is invalid SQL.
		$where = '';
		if ( array_key_exists( 's', $_GET ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$search = sanitize_text_field( wp_unslash( $_GET['s'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( '' !== $search ) {
				$where = $this->build_search_clause( $search );
			}
		}

		// Count every matching row so pagination reports the real total.
		$total_items = (int) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . $table . $where ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared

		$sql = 'SELECT * FROM ' . $table . $where;

		$order_by = '';
		$order    = '';
		if ( array_key_exists( 'orderby', $_GET ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$order_by = sanitize_text_field( wp_unslash( $_GET['orderby'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$order    = isset( $_GET['order'] ) ? sanitize_text_field( wp_unslash( $_GET['order'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}
		$sql .= $this->build_order_clause( $order_by, $order );

		$per_page     = self::PER_PAGE;
		$current_page = max( 1, $this->get_pagenum() );

		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
			)
		);

		$sql .= $wpdb->prepare( ' LIMIT %d OFFSET %d', $per_page, ( $current_page - 1 ) * $per_page );

		$sql_data = $wpdb->get_results( $sql ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared

		$data = array();
		foreach ( (array) $sql_data as $row ) {
			$data[] = array(
				'id'         => (int) $row->id,
				'ip'         => sanitize_text_field( $row->ip ),
				'path'       => sanitize_text_field( $row->path ),
				'referer'    => sanitize_text_field( $row->referer ),
				'user_agent' => sanitize_text_field( $row->user_agent ),
				'created'    => sanitize_text_field( $row->created ),
			);
		}

		$this->items = $data;
	}

	/**
	 * Appends an ORDER BY clause for a recognised sortable column.
	 *
	 * The column name is resolved through SORTABLE_COLUMN_MAP and the direction
	 * is narrowed to the literal 'ASC' or 'DESC', so neither value can carry
	 * user input into the query. An unrecognised column is ignored entirely.
	 *
	 * @since 3.15.4 Column names are whitelisted; unknown values no longer emit a bare direction.
	 * @param string $order_by Requested orderby query argument.
	 * @param string $order    Requested sort direction.
	 * @param string $sql      SQL query string built so far.
	 * @return string Modified SQL query string.
	 */
	public function manage_sorting( $order_by, $order, $sql ) {
		if ( ! isset( self::SORTABLE_COLUMN_MAP[ $order_by ] ) ) {
			return $sql;
		}

		return $sql . $this->build_order_clause( $order_by, $order );
	}

	/**
	 * Builds the ORDER BY clause for the row query.
	 *
	 * Always returns a clause, and always ends on `id`.
	 *
	 * Two reasons the sort must be total rather than just correct. LIMIT/OFFSET
	 * has no defined row order in SQL unless the ORDER BY distinguishes every
	 * row, so a sort that ties leaves the database free to return a row on two
	 * different pages and omit another entirely. `created` has one-second
	 * resolution and a bot crawl writes dozens of rows inside the same second,
	 * so ties here are routine rather than exotic. `id` is the primary key and
	 * breaks every tie.
	 *
	 * With no sort requested the rows come back in `id` order, which is the
	 * order the screen showed before pagination moved into SQL.
	 *
	 * @since 3.15.4
	 * @param string $order_by Requested orderby query argument.
	 * @param string $order    Requested sort direction.
	 * @return string ORDER BY clause, including the leading space.
	 */
	private function build_order_clause( $order_by, $order ) {
		$direction = ( 'DESC' === strtoupper( (string) $order ) ) ? 'DESC' : 'ASC';

		if ( ! isset( self::SORTABLE_COLUMN_MAP[ $order_by ] ) ) {
			return ' ORDER BY id ASC';
		}

		return ' ORDER BY ' . self::SORTABLE_COLUMN_MAP[ $order_by ] . ' ' . $direction . ', id ' . $direction;
	}

	/**
	 * Builds the prepared WHERE clause for a search term.
	 *
	 * @since 3.15.4
	 * @param string $search Search string.
	 * @return string Prepared WHERE clause, including the leading space.
	 */
	private function build_search_clause( $search ) {
		global $wpdb;
		$like = '%' . $wpdb->esc_like( $search ) . '%';
		return $wpdb->prepare(
			' WHERE (ip LIKE %s OR path LIKE %s OR referer LIKE %s OR user_agent LIKE %s OR created LIKE %s)',
			$like,
			$like,
			$like,
			$like,
			$like
		);
	}

	/**
	 * Handles search filtering of the logs table.
	 *
	 * @param string $search Search string.
	 * @param string $sql SQL query string.
	 * @return string Modified SQL query string.
	 */
	public function manage_search( $search, $sql ) {
		return $sql . $this->build_search_clause( $search );
	}

	/**
	 * Returns the columns for the logs table.
	 *
	 * @return array Column definitions.
	 */
	public function get_columns() {
		$columns = array(
			'cb'         => "<input type='checkbox' />",
			'ip'         => esc_html__( 'IP', 'custom-404-pro' ),
			'path'       => esc_html__( 'Path', 'custom-404-pro' ),
			'referer'    => esc_html__( 'Referer', 'custom-404-pro' ),
			'user_agent' => esc_html__( 'User Agent', 'custom-404-pro' ),
			'created'    => esc_html__( 'Created', 'custom-404-pro' ),
		);
		return $columns;
	}

	/**
	 * Renders the default column value.
	 *
	 * @param array  $item Row data.
	 * @param string $column_name Column name.
	 * @return mixed Column value.
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'cb':
				return (int) $item['id'];
			case 'ip':
			case 'path':
			case 'referer':
			case 'user_agent':
			case 'created':
				// WP_List_Table echoes column values verbatim, so escape here.
				return esc_html( $item[ $column_name ] );
		}
	}

	/**
	 * Returns the sortable columns.
	 *
	 * @return array Sortable column definitions.
	 */
	public function get_sortable_columns() {
		$sortable_columns = array(
			'ip'         => 'ip',
			'path'       => 'path',
			'referer'    => 'referer',
			'user_agent' => 'user_agent',
			'created'    => array( 'created', true ),
		);
		return $sortable_columns;
	}

	/**
	 * Renders the IP column with row actions.
	 *
	 * @param array $item Row data.
	 * @return string Column HTML.
	 */
	public function column_ip( $item ) {
		$page_slug   = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$delete_link = wp_nonce_url(
			add_query_arg(
				array(
					'page'   => $page_slug,
					'action' => 'c4p-logs--delete',
					'path'   => (int) $item['id'],
				),
				admin_url( 'admin.php' )
			),
			'c4p-logs--delete'
		);
		$actions     = array(
			'c4p-logs--delete' => sprintf(
				'<a href="%1$s">%2$s</a>',
				esc_url( $delete_link ),
				esc_html__( 'Delete', 'custom-404-pro' )
			),
		);
		return sprintf(
			'%1$s %2$s',
			esc_html( $item['ip'] ),
			$this->row_actions( $actions )
		);
	}

	/**
	 * Renders the checkbox column.
	 *
	 * @param array $item Row data.
	 * @return string Column HTML.
	 */
	public function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="path[]" value="%1$d" aria-label="%2$s" />',
			(int) $item['id'],
			/* translators: %s: the 404 path recorded in this log row */
			esc_attr( sprintf( __( 'Select log entry for %s', 'custom-404-pro' ), $item['path'] ) )
		);
	}

	/**
	 * Returns the bulk actions for the logs table.
	 *
	 * @return array Bulk action definitions.
	 */
	public function get_bulk_actions() {
		$actions = array(
			'c4p-logs--delete'     => esc_html__( 'Delete', 'custom-404-pro' ),
			'c4p-logs--delete-all' => esc_html__( 'Delete All', 'custom-404-pro' ),
			'c4p-logs--export-csv' => esc_html__( 'Export All (.csv)', 'custom-404-pro' ),
		);
		return $actions;
	}
}
