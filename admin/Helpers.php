<?php

class Helpers {

	private static $instance;

	public $table_options;
	public $table_logs;
	public $options_defaults;

	public static function singleton() {
		static $inst = null;
		if ( null === $inst ) {
			$inst = new Helpers();
		}
		return $inst;
	}

	public function __construct() {
		global $wpdb;
		$this->table_options    = 'custom_404_pro_options';
		$this->table_logs       = 'custom_404_pro_logs';
		$this->options_defaults = array();
		$options_defaults_temp  = array(
			'mode'                => '',
			'mode_page'           => '',
			'mode_url'            => '',
			'send_email'          => '',
			'logging_enabled'     => '',
			'redirect_error_code' => 302,
			'log_ip'              => true,
		);
		foreach ( $options_defaults_temp as $key => $value ) {
			$obj        = new stdClass();
			$obj->name  = $key;
			$obj->value = $value;
			array_push( $this->options_defaults, $obj );
		}
	}

	public function admin_notice( $type, $message ) {
		$html  = '';
		$html .= '<div class="notice notice-' . $type . '">';
		$html .= '   <p>' . $message . '</p>';
		$html .= '</div>';
		return $html;
	}

	public function initialize_table_options() {
		global $wpdb;
		if ( current_user_can( 'manage_options' ) ) {
			$count = count( $this->options_defaults );
			$sql   = 'INSERT INTO ' . $wpdb->prefix . $this->table_options . ' (name, value) VALUES '; // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			foreach ( $this->options_defaults as $key => $option ) {
				if ( $key !== ( $count - 1 ) ) {
					$sql .= "('" . $option->name . "', '" . $option->value . "'),";
				} else {
					$sql .= "('" . $option->name . "', '" . $option->value . "')";
				}
			}
			$wpdb->query( $sql ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared
		}
	}

	public function is_option( $option_name ) {
		global $wpdb;
		if ( current_user_can( 'manage_options' ) ) {
			$query  = $wpdb->prepare( 'SELECT * FROM ' . $wpdb->prefix . $this->table_options . ' WHERE name = %s', $option_name ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$result = $wpdb->get_results( $query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			if ( empty( $result ) ) {
				return false;
			} else {
				return $result[0];
			}
		}
	}

	public function get_option( $option_name ) {
		global $wpdb;
		if ( current_user_can( 'manage_options' ) ) {
			$query  = $wpdb->prepare( 'SELECT value FROM ' . $wpdb->prefix . $this->table_options . ' WHERE name = %s', $option_name ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$result = $wpdb->get_var( $query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			return $result;
		}
	}

	public function insert_option( $option_name, $option_value ) {
		global $wpdb;
		if ( current_user_can( 'manage_options' ) ) {
			$result = $wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
				$wpdb->prefix . $this->table_options,
				array(
					'name'  => $option_name,
					'value' => $option_value,
				)
			);
			return $result;
		}
	}

	public function update_option( $option_name, $option_value ) {
		global $wpdb;
		if ( current_user_can( 'manage_options' ) ) {
			$result = $wpdb->update( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->prefix . $this->table_options,
				array( 'value' => $option_value ),
				array( 'name' => $option_name )
			);
			return $result;
		}
	}

	public function upsert_option( $option_name, $option_value ) {
		global $wpdb;
		if ( current_user_can( 'manage_options' ) ) {
			if ( self::is_option( $option_name ) ) {
				$result = self::update_option( $option_name, $option_value );
			} else {
				$result = self::insert_option( $option_name, $option_value );
			}
			return $result;
		}
	}

	public function get_logs_columns() {
		global $wpdb;
		if ( current_user_can( 'manage_options' ) ) {
			$query  = 'SHOW COLUMNS FROM ' . $wpdb->prefix . $this->table_logs; // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$result = $wpdb->get_results( $query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			return $result;
		}
	}

	public function get_old_logs_count() {
		global $wpdb;
		if ( current_user_can( 'manage_options' ) ) {
			$query  = "SELECT COUNT(*) FROM {$wpdb->prefix}posts WHERE post_type='c4p_log'"; // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$result = $wpdb->get_var( $query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			return (int) $result;
		}
	}

	public function delete_old_logs( $log_ids ) {
		global $wpdb;
		if ( current_user_can( 'manage_options' ) ) {
			foreach ( $log_ids as $id ) {
				wp_delete_post( $id, true );
			}
		}
	}

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

	public function get_logs() {
		global $wpdb;
		if ( current_user_can( 'manage_options' ) ) {
			$query  = 'SELECT * FROM ' . $wpdb->prefix . $this->table_logs; // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$result = $wpdb->get_results( $query, ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			return $result;
		}
	}

	public function delete_logs( $path ) {
		global $wpdb;
		if ( current_user_can( 'manage_options' ) ) {
			if ( 'all' === $path ) {
				$query  = 'TRUNCATE TABLE ' . $wpdb->prefix . $this->table_logs; // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				$result = $wpdb->query( $query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			} elseif ( is_array( $path ) ) {
				$ids          = array_map( 'absint', $path );
				$placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
				$query        = $wpdb->prepare( 'DELETE FROM ' . $wpdb->prefix . $this->table_logs . ' WHERE id IN (' . $placeholders . ')', ...$ids ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.PreparedSQLPlaceholders
				$result       = $wpdb->query( $query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			} else {
				$query  = $wpdb->prepare( 'DELETE FROM ' . $wpdb->prefix . $this->table_logs . ' WHERE id = %d', $path ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				$result = $wpdb->query( $query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			}
			return $result;
		}
	}

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
