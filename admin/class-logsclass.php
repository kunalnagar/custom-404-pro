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
	 * Constructor.
	 */
	public function __construct() {
		global $status, $page;
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
	 */
	public function prepare_items() {
		global $wpdb;
		$columns               = self::get_columns();
		$hidden                = array();
		$sortable              = self::get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );
		$helpers               = Helpers::singleton();
		$sql                   = 'SELECT * FROM ' . $wpdb->prefix . $helpers->table_logs;

		if ( array_key_exists( 'orderby', $_GET ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$order_by = sanitize_text_field( wp_unslash( $_GET['orderby'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$order    = isset( $_GET['order'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_GET['order'] ) ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( ! empty( $order_by ) && ! empty( $order ) ) {
				$sql = self::manage_sorting( $order_by, $order, $sql );
			}
		}

		if ( array_key_exists( 's', $_GET ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$search = sanitize_text_field( wp_unslash( $_GET['s'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( ! empty( $search ) ) {
				$sql = self::manage_search( $search, $sql );
			}
		}

		$sql_data       = $wpdb->get_results( $sql ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		$data           = array();
		$sql_data_count = count( $sql_data );
		for ( $i = 0; $i < $sql_data_count; $i++ ) {
			$temp               = array();
			$temp['id']         = $sql_data[ $i ]->id;
			$temp['ip']         = sanitize_text_field( $sql_data[ $i ]->ip );
			$temp['path']       = sanitize_text_field( $sql_data[ $i ]->path );
			$temp['referer']    = sanitize_text_field( $sql_data[ $i ]->referer );
			$temp['user_agent'] = sanitize_text_field( $sql_data[ $i ]->user_agent );
			$temp['created']    = sanitize_text_field( $sql_data[ $i ]->created );
			array_push( $data, $temp );
		}
		$per_page     = 50;
		$current_page = $this->get_pagenum();
		$total_items  = count( $data );
		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
			)
		);
		$data        = array_slice( $data, ( ( $current_page - 1 ) * $per_page ), $per_page );
		$this->items = $data;
	}

	/**
	 * Handles sorting of the logs table.
	 *
	 * @param string $order_by Column to sort by.
	 * @param string $order Sort direction (ASC or DESC).
	 * @param string $sql SQL query string.
	 * @return string Modified SQL query string.
	 */
	public function manage_sorting( $order_by, $order, $sql ) {
		if ( 'created' === $order_by ) {
			$sql .= ' ORDER BY created';
		} elseif ( 'u' === $order_by ) {
			$sql .= ' ORDER BY user_agent';
		} elseif ( 'i' === $order_by ) {
			$sql .= ' ORDER BY ip';
		} elseif ( 'p' === $order_by ) {
			$sql .= ' ORDER BY path';
		} elseif ( 'r' === $order_by ) {
			$sql .= ' ORDER BY referer';
		}
		$sql .= ' ' . sanitize_sql_orderby( $order );
		return $sql;
	}

	/**
	 * Handles search filtering of the logs table.
	 *
	 * @param string $search Search string.
	 * @param string $sql SQL query string.
	 * @return string Modified SQL query string.
	 */
	public function manage_search( $search, $sql ) {
		global $wpdb;
		$like = '%' . $wpdb->esc_like( $search ) . '%';
		$sql .= $wpdb->prepare(
			' WHERE (ip LIKE %s OR path LIKE %s OR referer LIKE %s OR user_agent LIKE %s OR created LIKE %s)',
			$like,
			$like,
			$like,
			$like,
			$like
		);
		return $sql;
	}

	/**
	 * Returns the columns for the logs table.
	 *
	 * @return array Column definitions.
	 */
	public function get_columns() {
		$columns = array(
			'cb'         => "<input type='checkbox' />",
			'ip'         => 'IP',
			'path'       => 'Path',
			'referer'    => 'Referer',
			'user_agent' => 'User Agent',
			'created'    => 'Created',
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
				return $item['id'];
			case 'ip':
				return $item['ip'];
			case 'path':
				return $item['path'];
			case 'referer':
				return $item['referer'];
			case 'user_agent':
				return $item['user_agent'];
			case 'created':
				return $item['created'];
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
		$nonce     = wp_create_nonce( 'c4p-logs--delete' );
		$page_slug = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$actions   = array(
			'c4p-logs--delete' => sprintf( '<a href="?page=%s&action=%s&path=%s&_wpnonce=%s">Delete</a>', esc_html( $page_slug ), 'c4p-logs--delete', $item['id'], $nonce ),
		);
		return sprintf(
			'%1$s %2$s',
			/*$1%s*/
			$item['ip'],
			/*$2%s*/
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
		return '<input type="checkbox" name="path[]" value="' . $item['id'] . '" />';
	}

	/**
	 * Returns the bulk actions for the logs table.
	 *
	 * @return array Bulk action definitions.
	 */
	public function get_bulk_actions() {
		$actions = array(
			'c4p-logs--delete'     => 'Delete',
			'c4p-logs--delete-all' => 'Delete All',
			'c4p-logs--export-csv' => 'Export All (.csv)',
		);
		return $actions;
	}
}
