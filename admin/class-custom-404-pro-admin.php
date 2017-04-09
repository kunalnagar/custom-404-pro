<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://kunalnagar.in
 * @since      1.0.0
 *
 * @package    Custom_404_Pro
 * @subpackage Custom_404_Pro/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Custom_404_Pro
 * @subpackage Custom_404_Pro/admin
 * @author     Kunal Nagar <knlnagar@gmail.com>
 */
class Custom_404_Pro_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 *
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/custom-404-pro-admin.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/custom-404-pro-admin.js', array( 'jquery' ), $this->version, false );
	}

	// Create Plugin Admin Menu
	public function create_admin_menu() {
		add_menu_page( 'Custom 404 Pro', 'Custom 404 Pro', 'manage_options', 'c4p-main', null, 'dashicons-chart-bar' );
		add_submenu_page( 'c4p-main', 'Settings', 'Settings', 'manage_options', 'c4p-main', array(
			$this,
			'main_admin_menu_settings'
		) );
		add_submenu_page( 'c4p-main', 'More Info', 'More Info', 'manage_options', 'c4p-more-info', array(
			$this,
			'main_admin_menu_more_info'
		) );
	}

	public function c4p_count_logs() {
		$count = wp_count_posts('c4p_log');
		wp_send_json($count->publish);
		die();
	}

	public function c4p_clear_logs() {
		$args = array(
			'numberposts' => 100,
			'post_type'   => 'c4p_log'
		);
		$logs = array( 'abc' );
		while ( count( $logs ) > 0 ) {
			$logs = get_posts( $args );
			if ( is_array( $logs ) ) {
				foreach ( $logs as $log ) {
					wp_delete_post( $log->ID, true );
				}
			}
		}
		echo 'done';
		die();
	}

	// Register 404 Logs CPT
	public function register_logs_cpt() {
		$labels = array(
			'name'               => '404 Logs',
			'singular_name'      => 'Log',
			'add_new'            => 'Add New',
			'add_new_item'       => 'Add New Log',
			'edit_item'          => 'Edit Log',
			'new_item'           => 'New Log',
			'all_items'          => '404 Logs',
			'view_item'          => 'View Log',
			'search_items'       => 'Search Logs',
			'not_found'          => 'No logs found',
			'not_found_in_trash' => 'No logs found in the Trash',
			'parent_item_colon'  => '',
			'menu_name'          => 'Logs'
		);
		$args   = array(
			'labels'               => $labels,
			'description'          => 'A List of 404 Logs',
			'public'               => 'false',
			'publicly_queryable'   => 'false',
			'show_ui'              => 'true',
			'menu_icon'            => 'dashicons-chart-area',
			'show_in_menu'         => 'c4p-main',
			'supports'             => array( 'revisions' ),
			'register_meta_box_cb' => array( $this, 'logs_add_custom_fields_metabox' ),
			'capability_type'      => 'post',
			'capabilities'         => array( 'create_posts' => false ),
			'map_meta_cap'         => true
		);
		register_post_type( 'c4p_log', $args );
	}

	public function init_options() {
		if ( get_option( 'c4p_logging_status' ) == '' || get_option( 'c4p_logging_status' ) == null ) {
			update_option( 'c4p_logging_status', 'disabled' );
		}
	}

	// Add Custom Fields to 404 Logs CPT
	public function logs_add_custom_fields_metabox() {
		add_meta_box( 'c4p_log_custom_fields', 'Custom Fields', array(
			$this,
			'include_custom_fields_file'
		), 'c4p_log', 'normal', 'default' );
	}

	// Include External File that contains Custom Fields Markup
	public function include_custom_fields_file() {
		include 'partials/metaboxes/custom-404-pro-admin-log-custom-fields.php';
	}

	// Save Custom Fields Data to 404 Logs CPT
	public function logs_save_custom_fields_data( $post_id, $post ) {
		$temp = trim( $_POST['c4p_log_redirect_to'] );
		update_post_meta( $post->ID, 'c4p_log_redirect_to', $_POST['c4p_log_redirect_to'] );
	}

	// Recreate 404 Logs CPT Admin Table Columns for better control
	public function recreate_admin_table_columns( $columns ) {
		$new_columns['cb']          = '<input type="checkbox" />';
		$new_columns['ip']          = 'User IP';
		$new_columns['404_path']    = '404 Path';
		$new_columns['user_agent']  = 'User Agent';
		$new_columns['redirect_to'] = 'Redirect';
		$new_columns['date']        = 'Date';

		return $new_columns;
	}

	// Add Sorting to 404 Logs CPT Admin Table Columns
	public function add_sorting_to_admin_table_columns( $sortable_columns ) {
		$sortable_columns['ip']          = 'c4p_log_ip';
		$sortable_columns['404_path']    = 'c4p_log_404_path';
		$sortable_columns['user_agent']  = 'c4p_log_user_agent';
		$sortable_columns['redirect_to'] = 'c4p_log_redirect_to';

		return $sortable_columns;
	}

	// Show 404 Logs CPT Values for Individual Columns
	public function show_cpt_values_custom_columns( $column_name, $id ) {
		global $wpdb;
		switch ( $column_name ) {
			case 'ip':
				$ip = get_post_meta( $id, 'c4p_log_ip', true );
				echo '<b>' . $ip . '</b>';
				echo '<div class="row-actions"><span class="edit"><a href="' . admin_url() . 'post.php?post=' . $id . '&amp;action=edit" title="Edit this item">Edit</a> | </span><span class="trash"><a class="submitdelete" title="Move this item to the Trash" href="' . get_delete_post_link( $id ) . '">Trash</a>';
				break;

			case '404_path':
				$path = get_post_meta( $id, 'c4p_log_404_path', true );
				echo '<a href="' . $path . '" target="blank">' . $path . '</a>';;
				break;

			case 'user_agent':
				$user_agent = get_post_meta( $id, 'c4p_log_user_agent', true );
				echo $user_agent;
				break;

			case 'redirect_to':
				$redirect_to = get_post_meta( $id, 'c4p_log_redirect_to', true );
				$temp        = trim( $redirect_to );
				if ( empty( $temp ) ) {
					$is_selected_page = get_option( 'c4p_selected_page' );
					$url              = get_option( 'c4p_selected_url' );
					if ( ! empty( $is_selected_page ) ) {
						$selected_page = maybe_unserialize( get_option( 'c4p_selected_page' ) );
						echo 'Global Settings Apply (<a href="' . $selected_page->post_name . '" target="blank" title="' . $selected_page->post_title . '">Page</a>)';
					} else if ( ! empty( $url ) ) {
						echo 'Global Settings Apply (<a href="' . $url . '" target="blank">URL</a>)';
					} else {
						echo 'Default 404 Page';
					}
				} else {
					echo $redirect_to;
				}
				break;
		}
	}

	// Custom 404 Pro Main Settings Tab
	public function main_admin_menu_settings() {
		include 'partials/settings/custom-404-pro-admin-settings.php';
	}

	// Custom 404 Pro More Info Tab
	public function main_admin_menu_more_info() {
		include 'partials/more-info/custom-404-pro-admin-more-info.php';
	}

	// Save options for Global Redirect Settings (Mode)
	public function handle_settings_global_redirect_form() {
		if ( isset( $_POST['mode'] ) && ! empty( $_POST['mode'] ) ) {
			$mode = $_POST['mode'];
			switch ( $mode ) {
				case 'page':
					$page = get_post( $_POST['c4p_page'] );
					update_option( 'c4p_mode', 'page' );
					update_option( 'c4p_selected_page', maybe_serialize( $page ) );
					update_option( 'c4p_selected_url', '' );
					wp_redirect( admin_url( 'admin.php?page=c4p-main&message=updated-page' ) );
					break;

				case 'url':
					$url = $_POST['c4p_url'];
					update_option( 'c4p_mode', 'url' );
					update_option( 'c4p_selected_url', $url );
					update_option( 'c4p_selected_page', '' );
					wp_redirect( admin_url( 'admin.php?page=c4p-main&message=updated-url' ) );
					break;
			}
		} else {
			update_option( 'c4p_mode', '' );
			update_option( 'c4p_selected_url', '' );
			update_option( 'c4p_selected_page', '' );
			wp_redirect( admin_url( 'admin.php?page=c4p-main' ) );
		}
	}

	public function clear_logs() {
		$args = array(
			'numberposts' => - 1,
			'post_type'   => 'c4p_log'
		);
		$logs = get_posts( $args );
		if ( is_array( $logs ) ) {
			foreach ( $logs as $log ) {
				wp_delete_post( $log->ID, true );
			}
		}
	}

	// Save options for General Settings
	public function handle_settings_general_form() {
		if ( isset( $_POST['c4p_log_email'] ) ) {
			update_option( 'c4p_log_email', true );
		} else {
			update_option( 'c4p_log_email', false );
		}
		if ( isset( $_POST['c4p_clear_logs'] ) ) {
			$this->clear_logs();
		}
		if ( isset( $_POST['c4p_log_type'] ) ) {
			update_option( 'c4p_log_type', (int) sanitize_text_field( $_POST['c4p_log_type'] ) );
		}
		if ( $_POST['c4p_logging_status'] === 'enabled' ) {
			update_option( 'c4p_logging_status', 'enabled' );
		} else {
			update_option( 'c4p_logging_status', 'disabled' );
		}
		wp_redirect( admin_url( 'admin.php?page=c4p-main&tab=settings-general&message=settings_general_form-updated' ) );
	}

	// Create the 404 Log Email to be sent
	public function send_404_log_email( $log_meta ) {
		$admin_email = get_option( 'admin_email' );
		$headers[]   = 'From: Site Admin <' . $admin_email . '>' . "\r\n";
		$headers[]   = 'Content-Type: text/html; charset=UTF-8';
		$message     = '<p>Here are the 404 Log Details:</p>';
		$message .= '<table>';
		$message .= '<tr>';
		$message .= '<th>User IP</th>';
		$message .= '<td>' . $log_meta['ip'] . '</td>';
		$message .= '</tr>';
		$message .= '<tr>';
		$message .= '<th>404 Path</th>';
		$message .= '<td>' . $log_meta['404_path'] . '</td>';
		$message .= '</tr>';
		$message .= '<tr>';
		$message .= '<th>User Agent</th>';
		$message .= '<td>' . $log_meta['user_agent'] . '</td>';
		$message .= '</tr>';
		$message .= '</table>';
		$is_sent = wp_mail(
			$admin_email,
			'404 Error on Site',
			$message,
			$headers
		);
	}

	public function get_user_agent_from_api( $user_agent ) {
		$url = 'http://www.useragentstring.com/?uas=' . urlencode( $user_agent ) . '&getJSON=all';

		if ( function_exists( 'curl_version' ) ) {
			$ch = curl_init();
			curl_setopt( $ch, CURLOPT_URL, $url );
			curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 5 );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
			$contents = curl_exec( $ch );
			if ( curl_errno( $ch ) ) {
				$contents = '';
			} else {
				curl_close( $ch );
			}
			if ( ! is_string( $contents ) || ! strlen( $contents ) ) {
				$contents = '';
			}
			$result = json_decode( $contents );
		} else {
			$temp   = @file_get_contents( $url );
			$result = json_decode( $temp );
		}

		return $result;
	}

	// Main Hook to perform redirections on 404s
	public function custom_404() {
		// $user_agent_min = $this->getUserAgent();
		if ( is_404() ) {
			$is_email_send               = get_option( 'c4p_log_email' );
			$check_post_redirect_to_args = array(
				'meta_key'       => 'c4p_log_404_path',
				'meta_value'     => $_SERVER['REQUEST_URI'],
				'post_type'      => 'c4p_log',
				'posts_per_page' => - 1
			);
			$c4p_log_data                = get_posts( $check_post_redirect_to_args );

			if ( ! empty( $c4p_log_data ) ) {

				if ( $is_email_send ) {
					$log_meta_ip         = get_post_meta( $c4p_log_data[0]->ID, 'c4p_log_ip', true );
					$log_meta_404_path   = get_post_meta( $c4p_log_data[0]->ID, 'c4p_log_404_path', true );
					$log_meta_user_agent = get_post_meta( $c4p_log_data[0]->ID, 'c4p_log_user_agent', true );
					$log_meta            = array(
						'ip'         => $log_meta_ip,
						'404_path'   => $log_meta_404_path,
						'user_agent' => $log_meta_user_agent
					);
					$this->send_404_log_email( $log_meta );
				}

				$redirect_uri = get_post_meta( $c4p_log_data[0]->ID, 'c4p_log_redirect_to', true );

				if ( ! empty( $redirect_uri ) ) {
					wp_redirect( $redirect_uri, get_option( 'c4p_log_type' ) );
				} else {
					$selected_page_option = get_option( 'c4p_selected_page' );
					$url                  = get_option( 'c4p_selected_url' );

					if ( ! empty( $selected_page_option ) ) {
						$selected_page  = maybe_unserialize( $selected_page_option );
						$translated_url = Custom_404_Pro_Admin::get_translated_url( $selected_page );

						wp_redirect( $translated_url, get_option( 'c4p_log_type' ) );
					} else if ( ! empty( $url ) ) {
						wp_redirect( $url, get_option( 'c4p_log_type' ) );
					}
				}
			} else {
				$logging_status = get_option( 'c4p_logging_status' );
				if ( $logging_status == 'enabled' ) {
					if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
						$ip = $_SERVER['HTTP_CLIENT_IP'];
					} else if ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
						$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
					} else {
						$ip = $_SERVER['REMOTE_ADDR'];
					}
					$c4p_log_args = array(
						'post_title'  => 'Log',
						'post_status' => 'publish',
						'post_type'   => 'c4p_log',
						'post_author' => 1
					);
					$c4p_log_id   = wp_insert_post( $c4p_log_args );
					update_post_meta( $c4p_log_id, 'c4p_log_ip', $ip );
					update_post_meta( $c4p_log_id, 'c4p_log_404_path', $_SERVER['REQUEST_URI'] );
					update_post_meta( $c4p_log_id, 'c4p_log_user_agent', $_SERVER['HTTP_USER_AGENT'] );

					$user_agent_meta = (array) $this->get_user_agent_from_api( $_SERVER['HTTP_USER_AGENT'] );
					update_post_meta( $c4p_log_id, 'c4p_log_user_agent_meta_agent_type', $user_agent_meta['agent_type'] );
					update_post_meta( $c4p_log_id, 'c4p_log_user_agent_meta_agent_name', $user_agent_meta['agent_name'] );
					update_post_meta( $c4p_log_id, 'c4p_log_user_agent_meta_agent_version', $user_agent_meta['agent_version'] );
					update_post_meta( $c4p_log_id, 'c4p_log_user_agent_meta_os_type', $user_agent_meta['os_type'] );
					update_post_meta( $c4p_log_id, 'c4p_log_user_agent_meta_os_name', $user_agent_meta['os_name'] );
					update_post_meta( $c4p_log_id, 'c4p_log_user_agent_meta_os_version_name', $user_agent_meta['os_versionName'] );
					update_post_meta( $c4p_log_id, 'c4p_log_user_agent_meta_os_version_number', $user_agent_meta['os_versionNumber'] );
					update_post_meta( $c4p_log_id, 'c4p_log_user_agent_meta_os_producer', $user_agent_meta['os_producer'] );
					update_post_meta( $c4p_log_id, 'c4p_log_user_agent_meta_os_producer_url', $user_agent_meta['os_producerURL'] );
					update_post_meta( $c4p_log_id, 'c4p_log_user_agent_meta_linux_distribution', $user_agent_meta['linux_distribution'] );
					update_post_meta( $c4p_log_id, 'c4p_log_user_agent_meta_agent_language', $user_agent_meta['agent_language'] );
					update_post_meta( $c4p_log_id, 'c4p_log_user_agent_meta_agent_language_tag', $user_agent_meta['agent_languageTag'] );

					update_post_meta( $c4p_log_id, 'c4p_log_user_agent_meta', maybe_serialize( $user_agent_meta ) );
					update_post_meta( $c4p_log_id, 'c4p_log_redirect_to', '' );
					if ( $is_email_send ) {
						$log_meta_ip         = get_post_meta( $c4p_log_id, 'c4p_log_ip', true );
						$log_meta_404_path   = get_post_meta( $c4p_log_id, 'c4p_log_404_path', true );
						$log_meta_user_agent = get_post_meta( $c4p_log_id, 'c4p_log_user_agent', true );
						$log_meta            = array(
							'ip'         => $log_meta_ip,
							'404_path'   => $log_meta_404_path,
							'user_agent' => $log_meta_user_agent
						);
						$this->send_404_log_email( $log_meta );
					}
				}
				$is_selected_page = get_option( 'c4p_selected_page' );
				$url              = get_option( 'c4p_selected_url' );
				if ( ! empty( $is_selected_page ) ) {
					$selected_page  = maybe_unserialize( get_option( 'c4p_selected_page' ) );
					$translated_url = Custom_404_Pro_Admin::get_translated_url( $selected_page );

					wp_redirect( $translated_url, get_option( 'c4p_log_type' ) );
				} else if ( ! empty( $url ) ) {
					wp_redirect( $url, get_option( 'c4p_log_type' ) );
				}
			}
		}
	}

	public static function get_translated_url( $post ) {
		$wpml_translation_id = apply_filters( 'wpml_object_id', $post->ID, 'page' );
		if ( $wpml_translation_id ) {
			$post = get_post( $wpml_translation_id );
		}

		return get_permalink( $post );
	}

//	public static function get_user_agent($agent) {
//		if (stripos($agent, 'Firefox') !== false) {
//			$agent = 'Firefox';
//		}
//		elseif (stripos($agent, 'MSIE') !== false) {
//			$agent = 'IE';
//		}
//		elseif (stripos($agent, 'iPad') !== false) {
//			$agent = 'iPad';
//		}
//		elseif (stripos($agent, 'Android') !== false) {
//			$agent = 'Android';
//		}
//		elseif (stripos($agent, 'Chrome') !== false) {
//			$agent = 'Chrome';
//		}
//		elseif (stripos($agent, 'Safari') !== false) {
//			$agent = 'Safari';
//		}
//		return $agent;
//	}

	// Create Filter Dropdown
	public function create_log_filters() {
		global $wpdb, $typenow;
		if ( $typenow == 'c4p_log' ) {
			$agent_name_sql       = 'SELECT DISTINCT meta_value FROM ' . $wpdb->postmeta . ' WHERE meta_key = "c4p_log_user_agent_meta_agent_name"';
			$agent_name_fields    = $wpdb->get_results( $agent_name_sql, ARRAY_A );
			$agent_version_sql    = 'SELECT DISTINCT meta_value FROM ' . $wpdb->postmeta . ' WHERE meta_key = "c4p_log_user_agent_meta_agent_version"';
			$agent_version_fields = $wpdb->get_results( $agent_version_sql, ARRAY_A );
			$os_type_sql          = 'SELECT DISTINCT meta_value FROM ' . $wpdb->postmeta . ' WHERE meta_key = "c4p_log_user_agent_meta_os_type"';
			$os_type_fields       = $wpdb->get_results( $os_type_sql, ARRAY_A );
			include 'partials/filters/filter-user-agent.php';
		}
	}

	// Get Filter Results
	public function get_filter_results( $query ) {
		global $pagenow;
		if ( is_admin() && $pagenow === 'edit.php' ) {
			if ( $_GET['filter_reset'] === 'Reset' ) {
				wp_redirect( admin_url() . 'edit.php?post_type=c4p_log' );
				exit;
			}
			$meta_query_array = array();
			if ( ! empty( $_GET['ua_browser_names'] ) ) {
				$temp = array(
					'key'     => 'c4p_log_user_agent_meta_agent_name',
					'value'   => $_GET['ua_browser_names'],
					'compare' => '='
				);
				array_push( $meta_query_array, $temp );
			}
			if ( ! empty( $_GET['ua_browser_versions'] ) ) {
				$temp = array(
					'key'     => 'c4p_log_user_agent_meta_agent_version',
					'value'   => $_GET['ua_browser_versions'],
					'compare' => '='
				);
				array_push( $meta_query_array, $temp );
			}
			if ( ! empty( $_GET['ua_os_types'] ) ) {
				$temp = array(
					'key'     => 'c4p_log_user_agent_meta_os_type',
					'value'   => $_GET['ua_os_types'],
					'compare' => '='
				);
				array_push( $meta_query_array, $temp );
			}
			$query->set( 'meta_query', $meta_query_array );
		}
	}
}
