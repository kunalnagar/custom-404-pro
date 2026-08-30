<?php
/**
 * Helper utilities for the plugin.
 *
 * @package Custom_404_Pro
 */

/**
 * Helpers class.
 */
class Helpers {

	/**
	 * Singleton instance.
	 *
	 * @var Helpers
	 */
	private static $instance;

	/**
	 * Logs table name (without prefix).
	 *
	 * @var string
	 */
	public $table_logs;

	/**
	 * wp_options key used to store all plugin settings.
	 *
	 * @var string
	 */
	const OPTION_KEY = 'custom_404_pro_settings';

	/**
	 * Returns the singleton instance of this class.
	 *
	 * @return Helpers
	 */
	public static function singleton() {
		static $inst = null;
		if ( null === $inst ) {
			$inst = new Helpers();
		}
		return $inst;
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->table_logs = 'custom_404_pro_logs';
	}

	/**
	 * Returns the default values for all plugin settings.
	 *
	 * @since 3.12.9
	 * @return array
	 */
	public function defaults(): array {
		return array(
			'mode'                => '',
			'mode_page'           => '',
			'mode_url'            => '',
			'send_email'          => false,
			'logging_enabled'     => false,
			'redirect_error_code' => 302,
			'log_ip'              => true,
			'email_cooldown'      => 3600,
			'log_retention_count' => 0,
			'log_retention_days'  => 0,
		);
	}

	/**
	 * Returns all plugin settings, falling back to defaults for any missing keys.
	 *
	 * @since 3.12.9
	 * @return array
	 */
	public function get_settings(): array {
		$saved = get_option( self::OPTION_KEY );
		if ( ! is_array( $saved ) ) {
			return $this->defaults();
		}
		return array_merge( $this->defaults(), $saved );
	}

	/**
	 * Returns a single setting value by key.
	 *
	 * @since 3.12.9
	 * @param string $key Setting key.
	 * @return mixed Setting value, or the default for that key if not set.
	 */
	public function get_setting( string $key ) {
		$settings = $this->get_settings();
		return $settings[ $key ] ?? $this->defaults()[ $key ] ?? null;
	}

	/**
	 * Merges the supplied values into the current settings and persists them.
	 *
	 * Only the keys present in $new_settings are updated; all other settings
	 * retain their current values.
	 *
	 * @since 3.12.9
	 * @param array $new_settings Key/value pairs to update.
	 * @return bool True on success, false on failure.
	 */
	public function update_settings( array $new_settings ): bool {
		$merged = array_merge( $this->get_settings(), $new_settings );
		return (bool) update_option( self::OPTION_KEY, $merged );
	}

	/**
	 * Generates an admin notice HTML string.
	 *
	 * @param string $type    Notice type (success, error, warning, info).
	 * @param string $message Notice message.
	 * @return string HTML for the notice.
	 */
	public function admin_notice( $type, $message ) {
		$html  = '';
		$html .= '<div class="notice notice-' . $type . '">';
		$html .= '   <p>' . $message . '</p>';
		$html .= '</div>';
		return $html;
	}

	/**
	 * Returns the column definitions for the logs table.
	 *
	 * @return array|null Array of column objects, or null.
	 */
	public function get_logs_columns() {
		global $wpdb;
		$query  = 'SHOW COLUMNS FROM ' . $wpdb->prefix . $this->table_logs; // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$result = $wpdb->get_results( $query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		return $result;
	}

	/**
	 * Returns the count of legacy log posts.
	 *
	 * @return int Number of old log posts.
	 */
	public function get_old_logs_count() {
		global $wpdb;
		$query  = $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}posts WHERE post_type = %s", 'c4p_log' ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$result = $wpdb->get_var( $query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		return (int) $result;
	}

	/**
	 * Deletes legacy log posts by ID.
	 *
	 * @param array $log_ids Array of post IDs to delete.
	 */
	public function delete_old_logs( $log_ids ) {
		foreach ( $log_ids as $id ) {
			wp_delete_post( $id, true );
		}
	}

	/**
	 * Creates log entries in the logs table from legacy post data.
	 *
	 * @param array $logs_data      Array of log data objects.
	 * @param bool  $is_deleting_old Whether to delete legacy posts after migration.
	 * @return int|false Number of rows inserted, or false on error.
	 */
	public function create_logs( $logs_data, $is_deleting_old ) {
		global $wpdb;
		$log_ids = array();
		$result  = false;
		foreach ( $logs_data as $log ) {
			if ( ! empty( $log->id ) ) {
				array_push( $log_ids, $log->id );
			}
			$result = $wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
				$wpdb->prefix . $this->table_logs,
				array(
					'ip'         => $log->ip,
					'path'       => $log->path,
					'referer'    => $log->referer,
					'user_agent' => $log->user_agent,
				)
			);
		}
		if ( ! is_wp_error( $result ) ) {
			if ( ! empty( $is_deleting_old ) && $is_deleting_old ) {
				self::delete_old_logs( $log_ids );
			}
		}
		return $result;
	}

	/**
	 * Retrieves all log entries from the logs table.
	 *
	 * @return array|null Array of log rows, or null.
	 */
	public function get_logs() {
		global $wpdb;
		$query  = 'SELECT * FROM ' . $wpdb->prefix . $this->table_logs; // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$result = $wpdb->get_results( $query, ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		return $result;
	}

	/**
	 * Deletes log entries from the logs table.
	 *
	 * @param string|int|array $path 'all' to truncate, array of IDs for bulk delete, or single ID.
	 * @return int|false Number of rows affected, or false on error.
	 */
	public function delete_logs( $path ) {
		global $wpdb;
		if ( 'all' === $path ) {
			$query  = 'TRUNCATE TABLE ' . $wpdb->prefix . $this->table_logs; // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$result = $wpdb->query( $query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		} elseif ( is_array( $path ) ) {
			$ids          = array_map( 'absint', $path );
			$placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
			$query        = $wpdb->prepare( 'DELETE FROM ' . $wpdb->prefix . $this->table_logs . ' WHERE id IN (' . $placeholders . ')', ...$ids ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.PreparedSQLPlaceholders, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
			$result       = $wpdb->query( $query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		} else {
			$query  = $wpdb->prepare( 'DELETE FROM ' . $wpdb->prefix . $this->table_logs . ' WHERE id = %d', $path ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$result = $wpdb->query( $query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		}
		return $result;
	}

	/**
	 * Returns the total number of rows in the logs table.
	 *
	 * @since 3.14.0
	 * @return int Total log row count.
	 */
	public function get_logs_count(): int {
		global $wpdb;
		$result = $wpdb->get_var( 'SELECT COUNT(*) FROM ' . $wpdb->prefix . $this->table_logs ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		return (int) $result;
	}

	/**
	 * Prunes log entries according to the configured retention settings.
	 *
	 * Two independent passes are run:
	 *   1. Count pass: if log_retention_count > 0 and the table exceeds that cap,
	 *      the oldest rows (by `created` timestamp) are deleted until only
	 *      log_retention_count rows remain.
	 *   2. Age pass: if log_retention_days > 0, all rows older than that many days
	 *      are deleted.
	 *
	 * Returns the total number of rows deleted across both passes.
	 *
	 * @since 3.14.0
	 * @return int Total rows deleted.
	 */
	public function prune_logs(): int {
		global $wpdb;
		$options  = $this->get_settings();
		$deleted  = 0;
		$table    = $wpdb->prefix . $this->table_logs;

		$max_count = isset( $options['log_retention_count'] ) ? (int) $options['log_retention_count'] : 0;
		if ( $max_count > 0 ) {
			$total  = $this->get_logs_count();
			$excess = $total - $max_count;
			if ( $excess > 0 ) {
				$query    = $wpdb->prepare( 'DELETE FROM ' . $table . ' ORDER BY created ASC LIMIT %d', $excess ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				$deleted += (int) $wpdb->query( $query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
			}
		}

		$max_days = isset( $options['log_retention_days'] ) ? (int) $options['log_retention_days'] : 0;
		if ( $max_days > 0 ) {
			$query    = $wpdb->prepare( 'DELETE FROM ' . $table . ' WHERE created < DATE_SUB(NOW(), INTERVAL %d DAY)', $max_days ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$deleted += (int) $wpdb->query( $query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		}

		return $deleted;
	}

	/**
	 * Number of log rows read per batch when streaming a CSV export.
	 *
	 * @since 3.15.5
	 * @var int
	 */
	const EXPORT_BATCH_SIZE = 1000;

	/**
	 * Neutralises a value that a spreadsheet would interpret as a formula.
	 *
	 * Log rows contain attacker-supplied data: an request to a 404 URL can set
	 * any Referer or User-Agent it likes. A field beginning with =, +, -, @ or a
	 * control character is executed as a formula when the exported CSV is opened
	 * in Excel, LibreOffice or Google Sheets (CSV injection, CWE-1236). Prefixing
	 * the value with an apostrophe forces the spreadsheet to treat it as text.
	 *
	 * @since 3.15.5
	 * @param mixed $value Raw column value.
	 * @return string Value that is safe to write to a CSV cell.
	 */
	public function escape_csv_value( $value ): string {
		$value = (string) $value;

		if ( '' === $value ) {
			return $value;
		}

		if ( in_array( $value[0], array( '=', '+', '-', '@', "\t", "\r" ), true ) ) {
			$value = "'" . $value;
		}

		return $value;
	}

	/**
	 * Writes one row to an open CSV stream.
	 *
	 * $escape is passed explicitly for two reasons. PHP 8.4 deprecates calling
	 * fputcsv() without it — on a site with WP_DEBUG display enabled those
	 * notices would be emitted straight into the download and corrupt the file.
	 * And the historical default, a backslash escape, is a PHP quirk that no
	 * spreadsheet expects; passing an empty string disables it and produces
	 * plain RFC 4180 output, where a quote is escaped by doubling it.
	 *
	 * The empty-string escape has been accepted since PHP 7.4, which is the
	 * minimum this plugin declares.
	 *
	 * @since 3.15.5
	 * @param resource $handle Open stream to write to.
	 * @param array    $row    Values for one CSV record.
	 * @return void
	 */
	public function write_csv_row( $handle, array $row ) {
		fputcsv( $handle, $row, ',', '"', '' );
	}

	/**
	 * Exports all log entries as a CSV file download.
	 *
	 * Rows are streamed to the browser in batches rather than concatenated into
	 * a single string, so exporting a large log table does not exhaust PHP's
	 * memory limit. Values are written with fputcsv() so that embedded quotes,
	 * commas and newlines are quoted correctly, and each value is passed through
	 * escape_csv_value() first to defuse spreadsheet formula injection.
	 *
	 * @since 3.15.5 Streams in batches; values are CSV-quoted and formula-escaped.
	 */
	public function export_logs_csv() {
		global $wpdb;

		$filename = 'custom-404-pro-logs-' . gmdate( 'Y-m-d-His' ) . '.csv';

		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=' . $filename );

		$handle = fopen( 'php://output', 'w' );
		if ( false === $handle ) {
			return;
		}

		$columns = $this->get_logs_columns();
		$fields  = array();
		foreach ( (array) $columns as $column ) {
			$fields[] = $column->Field; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- Field is a wpdb column object property
		}
		if ( ! empty( $fields ) ) {
			$this->write_csv_row( $handle, $fields );
		}

		$table     = $wpdb->prefix . $this->table_logs;
		$offset    = 0;
		$row_count = 0;
		do {
			$rows = (array) $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
				$wpdb->prepare( 'SELECT * FROM ' . $table . ' ORDER BY id ASC LIMIT %d OFFSET %d', self::EXPORT_BATCH_SIZE, $offset ),
				ARRAY_A
			);
			$row_count = count( $rows );

			foreach ( $rows as $row ) {
				$this->write_csv_row( $handle, array_map( array( $this, 'escape_csv_value' ), $row ) );
			}

			$offset += self::EXPORT_BATCH_SIZE;
		} while ( self::EXPORT_BATCH_SIZE === $row_count );

		fclose( $handle );
		exit;
	}
}
