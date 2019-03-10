<?php

class AdminClass {

	public function create_menu() {
		add_menu_page("Custom 404 Pro", "Custom 404 Pro", "manage_options", "c4p-main", array($this, 'page_logs'), 'dashicons-chart-bar');
		add_submenu_page("c4p-main", "Logs", "Logs", "manage_options", "c4p-main", array($this, 'page_logs'));
		add_submenu_page("c4p-main", "Settings", "Settings", "manage_options", "c4p-settings", array($this, 'page_settings'));
		add_submenu_page("c4p-main", "About", "About", "manage_options", "c4p-about", array($this, 'page_about'));
		add_submenu_page("c4p-main", "Reset", "Reset", "manage_options", "c4p-reset", array($this, 'page_reset'));
		// add_submenu_page("c4p-main", "Privacy", "Privacy", "manage_options", "c4p-privacy", array($this, 'page_privacy'));
	}

	public function page_logs() {
		include "views/logs.php";
	}

	public function page_settings() {
		include "views/settings.php";
	}

	public function page_about() {
		include "views/about.php";
	}

	public function page_reset() {
		include "views/reset.php";
	}

	public function page_privacy() {
		include "views/privacy.php";
	}

	public function enqueue_styles() {
		$request = $_REQUEST['page'];
		if($request === 'c4p-settings' || $request === 'c4p-main' || $request === 'c4p-about') {
			wp_enqueue_style('custom-404-pro-admin-css', plugin_dir_url(__FILE__) . 'css/custom-404-pro-admin.css', array(), '3.0.0');
		}
	}

	public function enqueue_scripts() {
		$request = $_REQUEST['page'];
		if($request === 'c4p-settings' || $request === 'c4p-main') {
			wp_enqueue_script('custom-404-pro-admin-js', plugin_dir_url(__FILE__) . 'js/custom-404-pro-admin.js', array('jquery'), '3.0.0', false);
		}
	}

	public function form_settings_global_redirect() {
		global $wpdb;
		// print_r($_POST);
		// die();
		$mode = "";
		$mode_page = "";
		$mode_url = "";
		$table_options = $wpdb->prefix . "custom_404_pro_options";
		if($_POST["mode"] === "page") {
			$mode = "'page'";
			$mode_page = $_POST["mode_page"];
			$sql_mode_page = "UPDATE " . $table_options . " SET value = '" . $mode_page . "' WHERE name='mode_page'";
			$sql_mode_url = "UPDATE " . $table_options . " SET value = 'NULL' WHERE name='mode_url'";
		} else if($_POST["mode"] === "url") {
			$mode = "'url'";
			$mode_url = $_POST["mode_url"];
			$sql_mode_url = "UPDATE " . $table_options . " SET value = '" . $mode_url . "' WHERE name='mode_url'";
			$sql_mode_page = "UPDATE " . $table_options . " SET value = 'NULL' WHERE name='mode_page'";
		} else {
			$mode = "NULL";
			$sql_mode_page = "UPDATE " . $table_options . " SET value='NULL' WHERE name='mode_page'";
			$sql_mode_url = "UPDATE " . $table_options . " SET value='NULL' where name='mode_url'";
		}
		$sql_mode = "UPDATE " . $table_options . " SET value = " . $mode . " WHERE name='mode'";
		$wpdb->query($sql_mode);
		$wpdb->query($sql_mode_page);
		$wpdb->query($sql_mode_url);
		wp_redirect(admin_url("admin.php?page=c4p-settings&tab=global-redirect&message=updated"));
	}

	public function form_settings_general() {
		global $wpdb;
		// print_r($_POST);
		// die();
		$table_options = $wpdb->prefix . "custom_404_pro_options";
		$field_send_email = "";
		if(isset($_POST["send_email"]) && $_POST["send_email"] === "on") {
			$field_send_email = "TRUE";
		} else {
			$field_send_email = "FALSE";
		}
		$sql_send_email = "UPDATE " . $table_options . " SET value=" . $field_send_email . " WHERE name='send_email'";
		$field_logging_enabled = "";
		if(isset($_POST["logging_enabled"]) && $_POST["logging_enabled"] === "enabled") {
			$field_logging_enabled = "TRUE";
		} else {
			$field_logging_enabled = "FALSE";
		}
		$sql_logging_enabled = "UPDATE " . $table_options . " SET value=" . $field_logging_enabled . " WHERE name='logging_enabled'";
		$field_redirect_error_code = $_POST["redirect_error_code"];
		$sql_redirect_error_code = "UPDATE " . $table_options . " SET value=" . $field_redirect_error_code . " WHERE name='redirect_error_code'";
		$wpdb->query($sql_send_email);
		$wpdb->query($sql_logging_enabled);
		$wpdb->query($sql_redirect_error_code);
		wp_redirect(admin_url("admin.php?page=c4p-settings&tab=general&message=updated"));
	}

	public function custom_404_pro_redirect() {
		global $wpdb;
		$table_options = $wpdb->prefix . "custom_404_pro_options";
		if(is_404()) {
			$sql = "SELECT * FROM " . $table_options;
			$sql_result = $wpdb->get_results($sql);
			// echo "<pre>";
			// print_r($sql_result);
			$row_mode = $sql_result[0];
			$row_mode_page = $sql_result[1];
			$row_mode_url = $sql_result[2];
			$row_send_email = $sql_result[3];
			$row_logging_enabled = $sql_result[4];
			$row_redirect_error_code = $sql_result[5];
			if($row_logging_enabled->value) {
				self::custom_404_pro_log($row_send_email->value);
			}
			if($row_mode->value === "page") {
				$page = get_post($row_mode_page->value);
				wp_redirect($page->guid, $row_redirect_error_code->value);
			} else if($row_mode->value === "url") {
				wp_redirect($row_mode_url->value, $row_redirect_error_code->value);
			}
		}
	}

	private function custom_404_pro_log($is_email) {
		global $wpdb;
		if (!empty($_SERVER["HTTP_CLIENT_IP"])) {
			$ip = $_SERVER["HTTP_CLIENT_IP"];
		} else if (!empty($_SERVER["HTTP_X_FORWARDED_FOR"])) {
			$ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
		} else {
			$ip = $_SERVER["REMOTE_ADDR"];
		}
		$path = $_SERVER["REQUEST_URI"];
		$referer = $_SERVER["HTTP_REFERER"];
		$user_agent = $_SERVER["HTTP_USER_AGENT"];
		$table_logs = $wpdb->prefix . "custom_404_pro_logs";
		$sql_save = "INSERT INTO " . $table_logs . " (ip, path, referer, user_agent) VALUES ('$ip', '$path', '$referer', '$user_agent')";
		$wpdb->query($sql_save);
		if(!empty($is_email)) {
			self::custom_404_pro_send_mail($ip, $path, $referer, $user_agent);
		}
	}

	private function custom_404_pro_send_mail($ip, $path, $referer, $user_agent) {
		$admin_email = get_option('admin_email');
		if(is_multisite()) {
			global $blog_id;
			$current_blog_details = get_blog_details(array( 'blog_id' => $blog_id));
			$current_site_name = $current_blog_details->blogname;
		} else {
			$current_site_name = get_bloginfo('name');
		}
		$headers[]   = 'From: Site Admin <' . $admin_email . '>' . "\r\n";
		$headers[]   = 'Content-Type: text/html; charset=UTF-8';
		$message     = '<p>Here are the 404 Log Details:</p>';
		$message .= '<table>';
		$message .= '<tr>';
		$message .= '<th>Site</th>';
		$message .= '<td>' . $current_site_name . '</td>';
		$message .= '</tr>';
		$message .= '<tr>';
		$message .= '<th>User IP</th>';
		$message .= '<td>' . $ip . '</td>';
		$message .= '</tr>';
		$message .= '<tr>';
		$message .= '<th>404 Path</th>';
		$message .= '<td>' . $path . '</td>';
		$message .= '</tr>';
		$message .= '<tr>';
		$message .= '<th>Referer</th>';
		$message .= '<td>' . $referer . '</td>';
		$message .= '</tr>';
		$message .= '<tr>';
		$message .= '<th>User Agent</th>';
		$message .= '<td>' . $user_agent . '</td>';
		$message .= '</tr>';
		$message .= '</table>';
		$is_sent = wp_mail(
			$admin_email,
			'404 Error on Site',
			$message,
			$headers
		);
	}

	public function form_reset() {
		global $wpdb;
		$sql1 = "DELETE FROM wp_posts WHERE post_type='c4p-log'";
		$sql2 = "DELETE FROM wp_postmeta WHERE post_id NOT IN (SELECT id FROM wp_posts)";
		$sql3 = "DELETE FROM wp_term_relationships WHERE object_id NOT IN (SELECT id FROM wp_posts)";
		$wpdb->query($sql1);
		$wpdb->query($sql2);
		$wpdb->query($sql3);
		wp_redirect(admin_url("admin.php?page=c4p-reset&message=updated"));
	}
}