<?php

class Helpers {

	private static $instance;

	public static function singleton() {
		static $inst = null;
		if ( $inst === null ) {
			$inst = new Helpers();
		}
		return $inst;
	}

	public function __construct() {
		global $wpdb;
		$this->table_options    = $wpdb->prefix . 'custom_404_pro_options';
		$this->table_logs       = $wpdb->prefix . 'custom_404_pro_logs';
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

	// public function plugin_data() {
	// $plugin_main_file = dirname(__FILE__) . '/custom-404-pro/custom-404-pro.php';
	// $plugin_data = get_plugin_data($plugin_main_file);
	// return $plugin_data;
	// }

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
        if(current_user_can('administrator')) {
            $count = count( $this->options_defaults );
            $sql   = 'INSERT INTO ' . $this->table_options . ' (name, value) VALUES ';
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
        if(current_user_can('administrator')) {
    		$query  = 'SELECT * FROM ' . $this->table_options . " WHERE name='" . $option_name . "'";
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
        if(current_user_can('administrator')) {
    		$query  = 'SELECT value FROM ' . $this->table_options . " WHERE name='" . $option_name . "'";
    		$result = $wpdb->get_var( $query );
    		return $result;
        }
	}

	public function insert_option( $option_name, $option_value ) {
		global $wpdb;
        if(current_user_can('administrator')) {
    		$result = $wpdb->insert(
    			$this->table_options,
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
        if(current_user_can('administrator')) {
    		$result = $wpdb->update(
    			$this->table_options,
    			array( 'value' => $option_value ),
    			array( 'name' => $option_name )
    		);
    		return $result;
        }
	}

	public function upsert_option( $option_name, $option_value ) {
		global $wpdb;
        if(current_user_can('administrator')) {
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
        if(current_user_can('administrator')) {
    		$query  = 'SHOW COLUMNS FROM ' . $this->table_logs;
    		$result = $wpdb->get_results( $query );
    		return $result;
        }
	}

	public function get_old_logs_count() {
		global $wpdb;
        if(current_user_can('administrator')) {
    		$query  = 'SELECT COUNT(*) FROM ' . $wpdb->prefix . "posts WHERE post_type='c4p_log'";
    		$result = $wpdb->get_var( $query );
    		return (int) $result;
        }
	}

	public function delete_old_logs( $logIDs ) {
		global $wpdb;
        if(current_user_can('administrator')) {
    		foreach ( $logIDs as $id ) {
    			wp_delete_post( $id, true );
    		}
        }
	}

	public function create_logs( $logsData, $isDeletingOld ) {
		global $wpdb;
        if(current_user_can('administrator')) {
    		$count  = count( $logsData );
    		$logIDs = [];
    		$query  = 'INSERT INTO ' . $this->table_logs . ' (ip, path, referer, user_agent) VALUES';
    		foreach ( $logsData as $key => $log ) {
    			if ( ! empty( $log->id ) ) {
    				array_push( $logIDs, $log->id );
    			}
    			$query .= " ('$log->ip', '$log->path', '$log->referer', '$log->user_agent')";
    			if ( $key < $count - 1 ) {
    				$query .= ',';
    			}
    		}
    		$result = $wpdb->query( $query );
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
        if(current_user_can('administrator')) {
    		$query  = 'SELECT * from ' . $this->table_logs;
    		$result = $wpdb->get_results( $query, ARRAY_A );
    		return $result;
        }
	}

	public function delete_logs( $path ) {
		global $wpdb;
        if(current_user_can('administrator')) {
    		if ( $path === 'all' ) {
    			$query = 'TRUNCATE TABLE ' . $this->table_logs;
    		} elseif ( is_array( $path ) ) {
    			$query = 'DELETE FROM ' . $this->table_logs . ' WHERE id in (' . implode( ',', $path ) . ')';
    		} else {
    			$query = 'DELETE FROM ' . $this->table_logs . ' WHERE id=' . $path . '';
    		}
    		$result = $wpdb->query( $query );
    		return $result;
        }
	}

	public function export_logs_csv() {
        if(current_user_can('administrator')) {
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
