<?php
/**
 * Admin class for the plugin.
 *
 * @package Custom_404_Pro
 */

/**
 * Admin class.
 */
class AdminClass {

	/**
	 * Helpers instance.
	 *
	 * @var Helpers
	 */
	private $helpers;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->helpers = Helpers::singleton();
	}

	/**
	 * Registers the admin menu pages.
	 */
	public function create_menu() {
		if ( current_user_can( 'manage_options' ) ) {
			add_menu_page( 'Custom 404 Pro', 'Custom 404 Pro', 'manage_options', 'c4p-main', array( $this, 'page_logs' ), 'dashicons-chart-bar' );
			add_submenu_page( 'c4p-main', 'Logs', 'Logs', 'manage_options', 'c4p-main', array( $this, 'page_logs' ) );
			add_submenu_page( 'c4p-main', 'Settings', 'Settings', 'manage_options', 'c4p-settings', array( $this, 'page_settings' ) );
			add_submenu_page( 'c4p-main', 'About', 'About', 'manage_options', 'c4p-about', array( $this, 'page_about' ) );
		}
	}

	/**
	 * Renders the Logs admin page.
	 */
	public function page_logs() {
		require_once __DIR__ . '/class-logsclass.php';
		include 'views/logs.php';
	}

	/**
	 * Renders the Settings admin page.
	 */
	public function page_settings() {
		include 'views/settings.php';
	}

	/**
	 * Renders the About admin page.
	 */
	public function page_about() {
		include 'views/about.php';
	}

	/**
	 * Enqueues admin stylesheets for plugin pages.
	 */
	public function enqueue_styles() {
		if ( current_user_can( 'manage_options' ) ) {
			if ( array_key_exists( 'page', $_REQUEST ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$request = sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				if ( 'c4p-settings' === $request || 'c4p-main' === $request || 'c4p-about' === $request ) {
					wp_enqueue_style( 'custom-404-pro-admin-css', plugin_dir_url( __FILE__ ) . 'css/custom-404-pro-admin.css', array(), '3.2.0' );
				}
			}
		}
	}

	/**
	 * Enqueues admin scripts for plugin pages.
	 */
	public function enqueue_scripts() {
		if ( current_user_can( 'manage_options' ) ) {
			if ( array_key_exists( 'page', $_REQUEST ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$request = sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				if ( 'c4p-settings' === $request || 'c4p-main' === $request ) {
					wp_enqueue_script( 'custom-404-pro-admin-js', plugin_dir_url( __FILE__ ) . 'js/custom-404-pro-admin.js', array( 'jquery' ), '3.2.0', false );
				}
			}
		}
	}

	/**
	 * Displays admin notices passed via query string.
	 */
	public function custom_404_pro_notices() {
		$message      = '';
		$message_type = 'success';
		$html         = '';
		if ( current_user_can( 'manage_options' ) ) {
			if ( array_key_exists( 'c4pmessage', $_REQUEST ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$message = esc_html( urldecode( sanitize_text_field( wp_unslash( $_REQUEST['c4pmessage'] ) ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				if ( array_key_exists( 'c4pmessageType', $_REQUEST ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					$allowed_types  = array( 'success', 'error', 'warning', 'info' );
					$requested_type = sanitize_text_field( wp_unslash( $_REQUEST['c4pmessageType'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					$message_type   = in_array( $requested_type, $allowed_types, true ) ? $requested_type : 'info';
				}
				$html .= '<div class="notice notice-' . $message_type . ' is-dismissible">';
				$html .= '<p>' . $message . '</p>';
				$html .= '</div>';
				echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- content is already escaped above
			}
		}
	}

	/**
	 * Handles the global redirect settings form submission.
	 */
	public function form_settings_global_redirect() {
		$nonce = isset( $_POST['form-settings-global-redirect'] ) ? sanitize_text_field( wp_unslash( $_POST['form-settings-global-redirect'] ) ) : '';
		if ( check_admin_referer( 'form-settings-global-redirect', 'form-settings-global-redirect' ) && current_user_can( 'manage_options' ) ) {
			$mode = isset( $_POST['mode'] ) ? sanitize_text_field( wp_unslash( $_POST['mode'] ) ) : '';
			$page = isset( $_POST['mode_page'] ) ? sanitize_text_field( wp_unslash( $_POST['mode_page'] ) ) : '';
			$url  = isset( $_POST['mode_url'] ) ? sanitize_text_field( wp_unslash( $_POST['mode_url'] ) ) : '';
			self::update_mode( $mode, $page, $url );
			$message = rawurlencode( 'Saved!' );
			wp_safe_redirect( admin_url( 'admin.php?page=c4p-settings&tab=global-redirect&c4pmessage=' . $message . '&c4pmessageType=success' ) );
			exit;
		}
	}

	/**
	 * Handles the general settings form submission.
	 */
	public function form_settings_general() {
		$nonce = isset( $_POST['form-settings-general'] ) ? sanitize_text_field( wp_unslash( $_POST['form-settings-general'] ) ) : '';
		if ( check_admin_referer( 'form-settings-general', 'form-settings-general' ) && current_user_can( 'manage_options' ) ) {
			$send_email                = isset( $_POST['send_email'] ) ? sanitize_text_field( wp_unslash( $_POST['send_email'] ) ) : '';
			$logging_enabled           = isset( $_POST['logging_enabled'] ) ? sanitize_text_field( wp_unslash( $_POST['logging_enabled'] ) ) : '';
			$log_ip                    = isset( $_POST['log_ip'] ) ? sanitize_text_field( wp_unslash( $_POST['log_ip'] ) ) : '';
			$field_redirect_error_code = isset( $_POST['redirect_error_code'] ) ? absint( wp_unslash( $_POST['redirect_error_code'] ) ) : 302;
			$allowed_codes             = array( 301, 302, 307, 308 );
			$field_redirect_error_code = in_array( $field_redirect_error_code, $allowed_codes, true ) ? $field_redirect_error_code : 302;
			$allowed_cooldowns         = array( 900, 1800, 3600, 21600, 86400 );
			$raw_cooldown              = isset( $_POST['email_cooldown'] ) ? absint( wp_unslash( $_POST['email_cooldown'] ) ) : HOUR_IN_SECONDS;
			$field_email_cooldown      = in_array( $raw_cooldown, $allowed_cooldowns, true ) ? $raw_cooldown : HOUR_IN_SECONDS;
			$this->helpers->update_settings(
				array(
					'send_email'          => ( 'on' === $send_email ),
					'logging_enabled'     => ( 'enabled' === $logging_enabled ),
					'log_ip'              => ( 'on' === $log_ip ),
					'redirect_error_code' => $field_redirect_error_code,
					'email_cooldown'      => $field_email_cooldown,
				)
			);
			$message = rawurlencode( 'Saved!' );
			wp_safe_redirect( admin_url( 'admin.php?page=c4p-settings&tab=general&c4pmessage=' . $message . '&c4pmessageType=success' ) );
			exit;
		}
	}

	/**
	 * Handles admin-side log actions (delete, export).
	 */
	public function custom_404_pro_admin_init() {
		if ( current_user_can( 'manage_options' ) ) {
			if ( array_key_exists( 'action', $_REQUEST ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$action = sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$nonce  = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				if ( 'c4p-logs--delete' === $action && wp_verify_nonce( $nonce, 'c4p-logs--delete' ) ) {
					if ( array_key_exists( 'path', $_REQUEST ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
						$path = is_array( $_REQUEST['path'] ) ? array_map( 'absint', $_REQUEST['path'] ) : absint( $_REQUEST['path'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
						$this->helpers->delete_logs( $path );
						$message = rawurlencode( 'Log(s) successfully deleted!' );
						wp_safe_redirect( admin_url( 'admin.php?page=c4p-main&c4pmessage=' . $message . '&c4pmessageType=success' ) );
						exit;
					} else {
						$message = rawurlencode( 'Please select a few logs to delete and try again.' );
						wp_safe_redirect( admin_url( 'admin.php?page=c4p-main&c4pmessage=' . $message . '&c4pmessageType=warning' ) );
						exit;
					}
				} elseif ( 'c4p-logs--delete-all' === $action && wp_verify_nonce( $nonce, 'bulk-logs' ) ) {
					$this->helpers->delete_logs( 'all' );
					$message = rawurlencode( 'All Logs successfully deleted!' );
					wp_safe_redirect( admin_url( 'admin.php?page=c4p-main&c4pmessage=' . $message . '&c4pmessageType=success' ) );
					exit;
				} elseif ( 'c4p-logs--export-csv' === $action && wp_verify_nonce( $nonce, 'bulk-logs' ) ) {
					$this->helpers->export_logs_csv();
				}
			}
		}
	}

	/**
	 * Handles 404 detection and redirects.
	 */
	public function custom_404_pro_redirect() {
		if ( is_404() ) {
			$options = $this->helpers->get_settings();
			if ( ! empty( $options['logging_enabled'] ) ) {
				$email_cooldown = isset( $options['email_cooldown'] ) ? (int) $options['email_cooldown'] : HOUR_IN_SECONDS;
				self::custom_404_pro_log( $options['send_email'] ?? '', $email_cooldown );
			}
			if ( 'page' === ( $options['mode'] ?? '' ) ) {
				$page_id = $this->resolve_multilingual_page_id( (int) ( $options['mode_page'] ?? 0 ) );
				$page    = get_post( $page_id );
				if ( $page ) {
					if ( wp_safe_redirect( $page->guid, (int) ( $options['redirect_error_code'] ?? 302 ) ) ) {
						exit;
					}
				}
			} elseif ( 'url' === ( $options['mode'] ?? '' ) ) {
				if ( wp_safe_redirect( $options['mode_url'] ?? '', (int) ( $options['redirect_error_code'] ?? 302 ) ) ) {
					exit;
				}
			}
		}
	}

	/**
	 * Resolves the 404 redirect page ID for the current language.
	 *
	 * Checks for Polylang and WPML and returns the translated page ID when
	 * available, falling back to the original ID when no translation exists.
	 *
	 * @param int $page_id The configured page ID.
	 * @return int The resolved page ID for the current language.
	 */
	public function resolve_multilingual_page_id( int $page_id ): int {
		// Polylang support: redirect to the translated page for the current language.
		if ( function_exists( 'pll_get_post' ) ) {
			$translated_id = pll_get_post( $page_id, pll_current_language() );
			if ( $translated_id ) {
				$page_id = $translated_id;
			}
		}

		// WPML support: filter resolves the translated object ID (no-op when WPML is inactive).
		$page_id = (int) apply_filters( 'wpml_object_id', $page_id, 'page', true );

		return $page_id;
	}

	/**
	 * Logs a 404 event and optionally sends a notification email.
	 *
	 * @since 3.13.0 Added $email_cooldown parameter.
	 * @param bool $is_email        Whether to send a notification email.
	 * @param int  $email_cooldown  Cooldown period in seconds between notification emails.
	 */
	private function custom_404_pro_log( $is_email, $email_cooldown = HOUR_IN_SECONDS ) {
		global $wpdb;
		if ( empty( $this->helpers->get_setting( 'log_ip' ) ) ) {
			$ip = 'N/A';
		} elseif ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_CLIENT_IP'] ) );
		} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
		} else {
			$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		}
		$path    = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		$referer = '';
		if ( array_key_exists( 'HTTP_REFERER', $_SERVER ) ) {
			$referer = esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) );
		}
		$user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';
		$sql_save   = $wpdb->prepare( 'INSERT INTO ' . $wpdb->prefix . $this->helpers->table_logs . ' (ip, path, referer, user_agent) VALUES (%s, %s, %s, %s)', $ip, $path, $referer, $user_agent ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$wpdb->query( $sql_save ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		if ( ! empty( $is_email ) && ! $this->is_email_on_cooldown() ) {
			self::custom_404_pro_send_mail( $ip, $path, $referer, $user_agent );
			set_transient( 'custom_404_pro_email_cooldown', true, $email_cooldown );
		}
	}

	/**
	 * Checks whether the email notification cooldown is currently active.
	 *
	 * Returns true when a cooldown transient is set, meaning an email was already
	 * sent within the configured cooldown window and another should not be sent yet.
	 *
	 * @since 3.13.0
	 * @return bool True if cooldown is active, false if an email may be sent.
	 */
	public function is_email_on_cooldown(): bool {
		return (bool) get_transient( 'custom_404_pro_email_cooldown' );
	}

	/**
	 * Sends a 404 notification email to the admin.
	 *
	 * @param string $ip         User IP address.
	 * @param string $path       Requested 404 path.
	 * @param string $referer    HTTP referer.
	 * @param string $user_agent HTTP user agent.
	 */
	private function custom_404_pro_send_mail( $ip, $path, $referer, $user_agent ) {
		$admin_email = get_option( 'admin_email' );
		if ( is_multisite() ) {
			global $blog_id;
			$current_blog_details = get_blog_details( array( 'blog_id' => $blog_id ) );
			$current_site_name    = $current_blog_details->blogname;
		} else {
			$current_site_name = get_bloginfo( 'name' );
		}
		$headers[] = 'From: Site Admin <' . $admin_email . '>' . "\r\n";
		$headers[] = 'Content-Type: text/html; charset=UTF-8';
		$message   = '<p>Here are the 404 Log Details:</p>';
		$message  .= '<table>';
		$message  .= '<tr>';
		$message  .= '<th>Site</th>';
		$message  .= '<td>' . $current_site_name . '</td>';
		$message  .= '</tr>';
		$message  .= '<tr>';
		$message  .= '<th>User IP</th>';
		$message  .= '<td>' . $ip . '</td>';
		$message  .= '</tr>';
		$message  .= '<tr>';
		$message  .= '<th>404 Path</th>';
		$message  .= '<td>' . $path . '</td>';
		$message  .= '</tr>';
		$message  .= '<tr>';
		$message  .= '<th>Referer</th>';
		$message  .= '<td>' . $referer . '</td>';
		$message  .= '</tr>';
		$message  .= '<tr>';
		$message  .= '<th>User Agent</th>';
		$message  .= '<td>' . $user_agent . '</td>';
		$message  .= '</tr>';
		$message  .= '</table>';
		wp_mail(
			$admin_email,
			'404 Error on Site',
			$message,
			$headers
		);
	}

	/**
	 * Normalizes a page ID to its default-language equivalent.
	 *
	 * When WPML or Polylang is active the admin page dropdown is filtered to the
	 * current language, so the submitted page ID may be a translation rather than
	 * the original. Storing the default-language ID lets resolve_multilingual_page_id()
	 * derive the correct translation at redirect time, regardless of which language
	 * admin last saved the setting.
	 *
	 * @param int $page_id Page ID submitted by the settings form.
	 * @return int Default-language page ID, or the original ID when no multilingual plugin is active.
	 */
	private function normalize_page_id_to_default_language( int $page_id ): int {
		// WPML: translate submitted ID to the default language before storing.
		if ( has_filter( 'wpml_object_id' ) ) {
			$default_lang = apply_filters( 'wpml_default_language', null );
			$page_id      = (int) apply_filters( 'wpml_object_id', $page_id, 'page', true, $default_lang );
		}

		// Polylang: translate submitted ID to the default language before storing.
		if ( function_exists( 'pll_default_language' ) && function_exists( 'pll_get_post' ) ) {
			$translated = pll_get_post( $page_id, pll_default_language() );
			if ( $translated ) {
				$page_id = $translated;
			}
		}

		return $page_id;
	}

	/**
	 * Updates the redirect mode options.
	 *
	 * @param string $mode Mode value (page, url, or empty).
	 * @param string $page Page ID for page mode.
	 * @param string $url  URL for url mode.
	 */
	private function update_mode( $mode, $page, $url ) {
		if ( current_user_can( 'manage_options' ) ) {
			switch ( $mode ) {
				case 'page':
					$this->helpers->update_settings(
						array(
							'mode'      => 'page',
							'mode_page' => (string) $this->normalize_page_id_to_default_language( (int) $page ),
							'mode_url'  => '',
						)
					);
					break;
				case 'url':
					$this->helpers->update_settings(
						array(
							'mode'      => 'url',
							'mode_page' => '',
							'mode_url'  => $url,
						)
					);
					break;
				default:
					$this->helpers->update_settings(
						array(
							'mode'      => '',
							'mode_page' => '',
							'mode_url'  => '',
						)
					);
					break;
			}
		}
	}
}
