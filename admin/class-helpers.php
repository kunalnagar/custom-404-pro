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
		);
	}

	/**
	 * Returns all plugin settings, falling back to defaults for any missing keys.
	 *
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
		if ( current_user_can( 'manage_options' ) ) {
			$query  = 'SHOW COLUMNS FROM ' . $wpdb->prefix . $this->table_logs; // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$result = $wpdb->get_results( $query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
			return $result;
		}
	}

	/**
	 * Returns the count of legacy log posts.
	 *
	 * @return int Number of old log posts.
	 */
	public function get_old_logs_count() {
		global $wpdb;
		if ( current_user_can( 'manage_options' ) ) {
			$query  = $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}posts WHERE post_type = %s", 'c4p_log' ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$result = $wpdb->get_var( $query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
			return (int) $result;
		}
	}

	/**
	 * Deletes legacy log posts by ID.
	 *
	 * @param array $log_ids Array of post IDs to delete.
	 */
	public function delete_old_logs( $log_ids ) {
		if ( current_user_can( 'manage_options' ) ) {
			foreach ( $log_ids as $id ) {
				wp_delete_post( $id, true );
			}
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
		if ( current_user_can( 'manage_options' ) ) {
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
	}

	/**
	 * Retrieves all log entries from the logs table.
	 *
	 * @return array|null Array of log rows, or null.
	 */
	public function get_logs() {
		global $wpdb;
		if ( current_user_can( 'manage_options' ) ) {
			$query  = 'SELECT * FROM ' . $wpdb->prefix . $this->table_logs; // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$result = $wpdb->get_results( $query, ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
			return $result;
		}
	}

	/**
	 * Deletes log entries from the logs table.
	 *
	 * @param string|int|array $path 'all' to truncate, array of IDs for bulk delete, or single ID.
	 * @return int|false Number of rows affected, or false on error.
	 */
	public function delete_logs( $path ) {
		global $wpdb;
		if ( current_user_can( 'manage_options' ) ) {
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
	}

	/**
	 * Exports all log entries as a CSV file download.
	 */
	public function export_logs_csv() {
		if ( current_user_can( 'manage_options' ) ) {
			$filename   = 'logs_' . time() . '.csv';
			$csv_output = '';
			$columns    = self::get_logs_columns();
			if ( ! empty( $columns ) ) {
				foreach ( $columns as $column ) {
					$csv_output .= $column->Field . ', '; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- Field is a wpdb column object property
				}
			}
			$csv_output .= "\n";
			$results     = self::get_logs();
			if ( ! empty( $results ) ) {
				foreach ( $results as $result ) {
					foreach ( $result as $q ) {
						$csv_output .= '"' . $q . '", ';
					}
					$csv_output .= "\n";
				}
			}
			header( 'Content-Type: application/csv' );
			header( 'Content-Disposition: attachment; filename=' . $filename );
			header( 'Pragma: no-cache' );
			print $csv_output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CSV download, not HTML output
			exit;
		}
	}
}
