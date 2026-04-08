<?php

class Helpers {

	private static $instance;

	public $table_options;
	public $table_logs;
	public $options_defaults;

	public static function singleton() {
		static $inst = null;
		if ( $inst === null ) {
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

	public function print_pretty( $result ) {
		echo '<pre>';
		var_dump( $result );
		echo '</pre>';
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
		if ( current_user_can( 'administrator' ) ) {
			$count = count( $this->options_defaults );
			$sql   = 'INSERT INTO ' . $wpdb->prefix . $this->table_options . ' (name, value) VALUES ';
			foreach ( $this->options_defaults as $key => $option ) {
				if ( $key !== ( $count - 1 ) ) {
					$sql .= "('" . $option->name . "', '" . $option->value . "'),";
				} else {
					$sql .= "('" . $option->name . "', '" . $option->value . "')";
				}
			}
			$wpdb->query( $sql );
		}
	}

	public function is_option( $option_name ) {
		global $wpdb;
		if ( current_user_can( 'administrator' ) ) {
			$query  = $wpdb->prepare( 'SELECT * FROM ' . $wpdb->prefix . $this->table_options . ' WHERE name = %s', $option_name );
			$result = $wpdb->get_results( $query );
			if ( empty( $result ) ) {
				return false;
			} else {
				return $result[0];
			}
		}
	}

	public function get_option( $option_name ) {
		global $wpdb;
		if ( current_user_can( 'administrator' ) ) {
			$query  = $wpdb->prepare( 'SELECT value FROM ' . $wpdb->prefix . $this->table_options . ' WHERE name = %s', $option_name );
			$result = $wpdb->get_var( $query );
			return $result;
		}
	}

	public function insert_option( $option_name, $option_value ) {
		global $wpdb;
		if ( current_user_can( 'administrator' ) ) {
			$result = $wpdb->insert(
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
		if ( current_user_can( 'administrator' ) ) {
			$result = $wpdb->update(
				$wpdb->prefix . $this->table_options,
				array( 'value' => $option_value ),
				array( 'name' => $option_name )
			);
			return $result;
		}
	}

	public function upsert_option( $option_name, $option_value ) {
		global $wpdb;
		if ( current_user_can( 'administrator' ) ) {
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
		if ( current_user_can( 'administrator' ) ) {
			$query  = 'SHOW COLUMNS FROM ' . $wpdb->prefix . $this->table_logs;
			$result = $wpdb->get_results( $query );
			return $result;
		}
	}

	public function get_old_logs_count() {
		global $wpdb;
		if ( current_user_can( 'administrator' ) ) {
			$query  = 'SELECT COUNT(*) FROM ' . $wpdb->prefix . "posts WHERE post_type='c4p_log'";
			$result = $wpdb->get_var( $query );
			return (int) $result;
		}
	}

	public function delete_old_logs( $logIDs ) {
		global $wpdb;
		if ( current_user_can( 'administrator' ) ) {
			foreach ( $logIDs as $id ) {
				wp_delete_post( $id, true );
			}
		}
	}

	public function create_logs( $logsData, $isDeletingOld ) {
		global $wpdb;
		if ( current_user_can( 'administrator' ) ) {
			$logIDs = array();
			$result = false;
			foreach ( $logsData as $log ) {
				if ( ! empty( $log->id ) ) {
					array_push( $logIDs, $log->id );
				}
				$result = $wpdb->insert(
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
				if ( ! empty( $isDeletingOld ) && $isDeletingOld ) {
					self::delete_old_logs( $logIDs );
				}
			}
			return $result;
		}
	}

	public function get_logs() {
		global $wpdb;
		if ( current_user_can( 'administrator' ) ) {
			$query  = 'SELECT * from ' . $wpdb->prefix . $this->table_logs;
			$result = $wpdb->get_results( $query, ARRAY_A );
			return $result;
		}
	}

	public function delete_logs( $path ) {
		global $wpdb;
		if ( current_user_can( 'administrator' ) ) {
			if ( $path === 'all' ) {
				$query  = 'TRUNCATE TABLE ' . $wpdb->prefix . $this->table_logs;
				$result = $wpdb->query( $query );
			} elseif ( is_array( $path ) ) {
				$ids          = array_map( 'absint', $path );
				$placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
				$query        = $wpdb->prepare( 'DELETE FROM ' . $wpdb->prefix . $this->table_logs . ' WHERE id IN (' . $placeholders . ')', ...$ids );
				$result       = $wpdb->query( $query );
			} else {
				$query  = $wpdb->prepare( 'DELETE FROM ' . $wpdb->prefix . $this->table_logs . ' WHERE id = %d', $path );
				$result = $wpdb->query( $query );
			}
			return $result;
		}
	}

	public function export_logs_csv() {
		if ( current_user_can( 'administrator' ) ) {
			$filename   = 'logs_' . time() . '.csv';
			$csv_output = '';
			$columns    = self::get_logs_columns();
			if ( count( $columns ) > 0 ) {
				foreach ( $columns as $column ) {
					$csv_output .= $column->Field . ', ';
				}
			}
			$csv_output .= "\n";
			$results     = self::get_logs();
			if ( count( $results ) > 0 ) {
				foreach ( $results as $result ) {
					foreach ( $result as $q ) {
						$csv_output .= "\"$q\"" . ', ';
					}
					$csv_output .= "\n";
				}
			}
			header( 'Content-Type: application/csv' );
			header( 'Content-Disposition: attachment; filename=' . $filename );
			header( 'Pragma: no-cache' );
			print $csv_output;
			exit;
		}
	}
}
