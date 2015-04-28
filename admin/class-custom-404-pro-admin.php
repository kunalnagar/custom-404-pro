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
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param string  $plugin_name The name of this plugin.
	 * @param string  $version     The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
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
		add_submenu_page( 'c4p-main', 'Settings', 'Settings', 'manage_options', 'c4p-main', array( $this, 'main_admin_menu_settings' ) );
		add_submenu_page( 'c4p-main', 'More Info', 'More Info', 'manage_options', 'c4p-more-info', array( $this, 'main_admin_menu_more_info' ) );
	}

	// Register 404 Logs CPT
	public function register_logs_cpt() {
		$labels = array(
			'name' => '404 Logs',
			'singular_name' => 'Log',
			'add_new' => 'Add New',
			'add_new_item' => 'Add New Log',
			'edit_item' => 'Edit Log',
			'new_item' => 'New Log',
			'all_items' => '404 Logs',
			'view_item' => 'View Log',
			'search_items' => 'Search Logs',
			'not_found' => 'No logs found',
			'not_found_in_trash' => 'No logs found in the Trash',
			'parent_item_colon' => '',
			'menu_name' => 'Logs'
		);
		$args = array(
			'labels' => $labels,
			'description' => 'A List of 404 Logs',
			'public' => 'true',
			'show_ui' => 'true',
			'menu_icon' => 'dashicons-chart-area',
			'show_in_menu' => 'c4p-main',
			'supports' => array( 'revisions' ),
			'register_meta_box_cb' => array( $this, 'logs_add_metaboxes' ),
			'capability_type' => 'post',
			'capabilities' => array( 'create_posts' => false ),
			'map_meta_cap' => true
		);
		register_post_type( 'c4p_log', $args );
	}

	// Add Custom Fields to 404 Logs CPT
	public function logs_add_custom_fields_metabox() {
		add_meta_box( 'c4p_log_custom_fields', 'Custom Fields', array( $this, 'include_custom_fields_file' ), 'c4p_log', 'normal', 'default' );
	}

	// Include External File that contains Custom Fields Markup
	public function include_custom_fields_file() {
		include 'partials/metaboxes/custom-404-pro-admin-log-custom-fields.php';
	}

	// Save Custom Fields Data to 404 Logs CPT
	public function logs_save_custom_fields_data( $post_id, $post ) {
		$temp = trim( $_POST['c4p_log_redirect_to'] );
		if ( !empty( $temp ) ) {
			$logs_meta['c4p_log_redirect_to'] = $_POST['c4p_log_redirect_to'];
		}
		foreach ( $logs_meta as $key => $value ) {
			if ( $post->post_type === 'revision' ) return;
			update_post_meta( $post->ID, $key, $value );
		}
	}

	// Recreate 404 Logs CPT Admin Table Columns for better control
	public function recreate_admin_table_columns( $columns ) {
		$new_columns['cb'] = '<input type="checkbox" />';
		$new_columns['ip'] = 'User IP';
		$new_columns['404_path'] = '404 Path';
		$new_columns['user_agent'] = 'User Agent';
		$new_columns['redirect_to'] = 'Redirect';
		$new_columns['date'] = 'Date';
		return $new_columns;
	}

	// Add Sorting to 404 Logs CPT Admin Table Columns
	public function add_sorting_to_admin_table_columns( $sortable_columns ) {
		$sortable_columns['ip'] = 'c4p_log_ip';
		$sortable_columns['404_path'] = 'c4p_log_404_path';
		$sortable_columns['user_agent'] = 'c4p_log_user_agent';
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
			$temp = trim( $redirect_to );
			if ( empty( $temp ) ) {
				$is_selected_page = get_option( 'c4p_selected_page' );
				$url = get_option( 'c4p_selected_url' );
				if ( !empty( $is_selected_page ) ) {
					$selected_page = maybe_unserialize( get_option( 'c4p_selected_page' ) );
					echo 'Global Settings Apply (<a href="' . $selected_page->post_name . '" target="blank" title="' . $selected_page->post_title . '">Page</a>)';
				}
				else if ( !empty( $url ) ) {
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
		if ( isset( $_POST['mode'] ) && !empty( $_POST['mode'] ) ) {
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
		}
		else {
			update_option( 'c4p_mode', '' );
			update_option( 'c4p_selected_url', '' );
			update_option( 'c4p_selected_page', '' );
			wp_redirect( admin_url( 'admin.php?page=c4p-main' ) );
		}
	}

	// Save options for General Settings
	public function handle_settings_general_form() {
		if ( isset( $_POST['c4p_log_email'] ) ) {
			update_option( 'c4p_log_email', true );
		} else {
			update_option( 'c4p_log_email', false );
		}
		wp_redirect( admin_url( 'admin.php?page=c4p-main&tab=settings-general&message=settings_general_form-updated' ) );
	}

	// Create the 404 Log Email to be sent
	public function send_404_log_email( $log_meta ) {
		$admin_email = get_option( 'admin_email' );
		$headers[] = 'From: Site Admin <' . $admin_email . '>' . "\r\n";
		$headers[] = 'Content-Type: text/html; charset=UTF-8';
		$message  =  '<p>Here are the 404 Log Details:</p>';
		$message  .=  '<table>';
		$message .=  '<tr>';
		$message .=   '<th>User IP</th>';
		$message .=   '<td>' . $log_meta['ip'] . '</td>';
		$message .=  '</tr>';
		$message .=  '<tr>';
		$message .=   '<th>404 Path</th>';
		$message .=   '<td>' . $log_meta['404_path'] . '</td>';
		$message .=  '</tr>';
		$message .=  '<tr>';
		$message .=   '<th>User Agent</th>';
		$message .=   '<td>' . $log_meta['user_agent'] . '</td>';
		$message .=  '</tr>';
		$message .= '</table>';
		$is_sent = wp_mail(
			$admin_email,
			'404 Error on Site',
			$message,
			$headers
		);
	}

	// Main Hook to perform redirections on 404s
	public function custom_404() {
		// $user_agent_min = $this->getUserAgent();
		if ( is_404() ) {
			$is_email_send = get_option( 'c4p_log_email' );
			$check_post_redirect_to_args = array( 'meta_key' => 'c4p_log_404_path', 'meta_value' => $_SERVER['REQUEST_URI'], 'post_type' => 'c4p_log', 'posts_per_page' => -1 );
			$c4p_log_data = get_posts( $check_post_redirect_to_args );
			if ( !empty( $c4p_log_data ) ) {
				$redirect_uri = get_post_meta( $c4p_log_data[0]->ID, 'c4p_log_redirect_to', true );
				if ( $is_email_send ) {
					$log_meta_ip = get_post_meta( $c4p_log_data[0]->ID, 'c4p_log_ip', true );
					$log_meta_404_path = get_post_meta( $c4p_log_data[0]->ID, 'c4p_log_404_path', true );
					$log_meta_user_agent = get_post_meta( $c4p_log_data[0]->ID, 'c4p_log_user_agent', true );
					$log_meta = array(
						'ip' => $log_meta_ip,
						'404_path' => $log_meta_404_path,
						'user_agent' => $log_meta_user_agent
					);
					$this->send_404_log_email( $log_meta );
				}
				if ( !empty( $redirect_uri ) ) {
					wp_redirect( $redirect_uri );
				} else {
					$is_selected_page = get_option( 'c4p_selected_page' );
					$url = get_option( 'c4p_selected_url' );
					if ( !empty( $is_selected_page ) ) {
						$selected_page = maybe_unserialize( get_option( 'c4p_selected_page' ) );
						wp_redirect( site_url() . '/' . $selected_page->post_name );
					}
					else if ( !empty( $url ) ) {
							wp_redirect( $url );
						}
				}
			}
			else {
				if ( !empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
					$ip = $_SERVER['HTTP_CLIENT_IP'];
				}
				else if ( !empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
						$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
					}
				else {
					$ip = $_SERVER['REMOTE_ADDR'];
				}
				$c4p_log_args = array( 'post_title' => 'Log', 'post_status' => 'publish', 'post_type' => 'c4p_log', 'post_author' => 1 );
				$c4p_log_id = wp_insert_post( $c4p_log_args );
				update_post_meta( $c4p_log_id, 'c4p_log_ip', $ip );
				update_post_meta( $c4p_log_id, 'c4p_log_404_path', $_SERVER['REQUEST_URI'] );
				update_post_meta( $c4p_log_id, 'c4p_log_user_agent', $_SERVER['HTTP_USER_AGENT'] );
				update_post_meta( $c4p_log_id, 'c4p_log_redirect_to', '' );
				if ( $is_email_send ) {
					$log_meta_ip = get_post_meta( $c4p_log_id, 'c4p_log_ip', true );
					$log_meta_404_path = get_post_meta( $c4p_log_id, 'c4p_log_404_path', true );
					$log_meta_user_agent = get_post_meta( $c4p_log_id, 'c4p_log_user_agent', true );
					$log_meta = array(
						'ip' => $log_meta_ip,
						'404_path' => $log_meta_404_path,
						'user_agent' => $log_meta_user_agent
					);
					$this->send_404_log_email( $log_meta );
				}
				$is_selected_page = get_option( 'c4p_selected_page' );
				$url = get_option( 'c4p_selected_url' );
				if ( !empty( $is_selected_page ) ) {
					$selected_page = maybe_unserialize( get_option( 'c4p_selected_page' ) );
					wp_redirect( site_url() . '/' . $selected_page->post_name );
				}
				else if ( !empty( $url ) ) {
						wp_redirect( $url );
					}
			}
		}
	}

	public static function get_user_agent($agent) {
		if (stripos($agent, 'Firefox') !== false) {
			$agent = 'Firefox';
		}
		elseif (stripos($agent, 'MSIE') !== false) {
			$agent = 'IE';
		}
		elseif (stripos($agent, 'iPad') !== false) {
			$agent = 'iPad';
		}
		elseif (stripos($agent, 'Android') !== false) {
			$agent = 'Android';
		}
		elseif (stripos($agent, 'Chrome') !== false) {
			$agent = 'Chrome';
		}
		elseif (stripos($agent, 'Safari') !== false) {
			$agent = 'Safari';
		}
		return $agent;
	}

	// Create Filter Dropdown
	public function create_c4p_log_filters() {
		global $wpdb;
		$data = array(
			'c4p_log_user_agent'
		);
		foreach($data as $filter) {
			$sql = 'SELECT DISTINCT meta_value FROM ' . $wpdb->postmeta . ' WHERE meta_key = "' . $filter . '"';
			$fields = $wpdb->get_results($sql, ARRAY_A);
			if($filter == 'c4p_log_user_agent')
				include 'partials/filters/filter-user-agent.php';
		}
	}

	// Get Filter Results
	public function get_c4p_log_filter_results($query) {
		global $pagenow;
		if(is_admin() && $pagenow === 'edit.php' && isset($_GET['c4p_user_agent']) && $_GET['c4p_user_agent'] !== '') {
			$query->set('meta_key', 'c4p_log_user_agent');
			$query->set('meta_value', $_GET['c4p_user_agent']);
		}
	}
}
