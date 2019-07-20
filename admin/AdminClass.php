<?php

class AdminClass {

    public function __construct() {
        $this->helpers = Helpers::singleton();
    }

    public function create_menu() {
        if(current_user_can('administrator')) {
            add_menu_page( 'Custom 404 Pro', 'Custom 404 Pro', 'manage_options', 'c4p-main', array( $this, 'page_logs' ), 'dashicons-chart-bar' );
            add_submenu_page( 'c4p-main', 'Logs', 'Logs', 'manage_options', 'c4p-main', array( $this, 'page_logs' ) );
            add_submenu_page( 'c4p-main', 'Settings', 'Settings', 'manage_options', 'c4p-settings', array( $this, 'page_settings' ) );
            add_submenu_page( 'c4p-main', 'About', 'About', 'manage_options', 'c4p-about', array( $this, 'page_about' ) );
        }
    }

    public function page_logs() {
        include 'views/logs.php';
    }

    public function page_settings() {
        include 'views/settings.php';
    }

    public function page_about() {
        include 'views/about.php';
    }

    public function enqueue_styles() {
        if(current_user_can('administrator')) {
            if ( array_key_exists( 'page', $_REQUEST ) ) {
                $request = sanitize_text_field($_REQUEST['page']);
                if ( $request === 'c4p-settings' || $request === 'c4p-main' || $request === 'c4p-about' ) {
                    wp_enqueue_style( 'custom-404-pro-admin-css', plugin_dir_url( __FILE__ ) . 'css/custom-404-pro-admin.css', array(), '3.2.0' );
                }
            }
        }
    }

    public function enqueue_scripts() {
        if(current_user_can('administrator')) {
            if ( array_key_exists( 'page', $_REQUEST ) ) {
                $request = sanitize_text_field($_REQUEST['page']);
                if ( $request === 'c4p-settings' || $request === 'c4p-main' ) {
                    wp_enqueue_script( 'custom-404-pro-admin-js', plugin_dir_url( __FILE__ ) . 'js/custom-404-pro-admin.js', array( 'jquery' ), '3.2.0', false );
                }
            }
        }
    }

    public function custom_404_pro_notices() {
        $message     = '';
        $messageType = 'success';
        $html        = '';
        if(current_user_can('administrator')) {
            if ( array_key_exists( 'c4pmessage', $_REQUEST ) ) {
                $message = urldecode( sanitize_text_field($_REQUEST['c4pmessage']) );
                if ( array_key_exists( 'c4pmessageType', $_REQUEST ) ) {
                    $messageType = sanitize_text_field($_REQUEST['c4pmessageType']);
                }
                $html .= '<div class="notice notice-' . $messageType . ' is-dismissible">';
                $html .= '<p>' . $message . '</p>';
                $html .= '</div>';
                echo $html;
            }
        }
    }

    public function form_settings_global_redirect() {
        global $wpdb;
        if(wp_verify_nonce($_POST['form-settings-global-redirect'], 'form-settings-global-redirect') && check_admin_referer("form-settings-global-redirect", "form-settings-global-redirect") && current_user_can('administrator')) {
            $mode = sanitize_text_field($_POST['mode']);
            $page = sanitize_text_field($_POST['mode_page']);
            $url  = sanitize_text_field($_POST['mode_url']);
            self::update_mode( $mode, $page, $url );
            $message = urlencode( 'Saved!' );
            wp_redirect( admin_url( 'admin.php?page=c4p-settings&tab=global-redirect&c4pmessage=' . $message . '&c4pmessageType=success' ) );
        }
    }

    public function form_settings_general() {
        global $wpdb;
        if(wp_verify_nonce($_POST['form-settings-general'], 'form-settings-general') && check_admin_referer("form-settings-general", "form-settings-general") && current_user_can('administrator')) {
            $send_email = sanitize_text_field($_POST['send_email']);
            $logging_enabled = sanitize_text_field($_POST['logging_enabled']);
            $log_ip = sanitize_text_field($_POST['log_ip']);
            $field_redirect_error_code = sanitize_text_field($_POST['redirect_error_code']);
            if ( isset( $send_email ) && $send_email === 'on' ) {
                $field_send_email = true;
            } else {
                $field_send_email = false;
            }
            if ( isset( $logging_enabled ) && $logging_enabled === 'enabled' ) {
                $field_logging_enabled = true;
            } else {
                $field_logging_enabled = false;
            }
            if ( isset( $log_ip ) && $log_ip === 'on' ) {
                $field_log_ip = true;
            } else {
                $field_log_ip = false;
            }
            $this->helpers->update_option( 'send_email', $field_send_email );
            $this->helpers->update_option( 'logging_enabled', $field_logging_enabled );
            $this->helpers->update_option( 'redirect_error_code', $field_redirect_error_code );
            // New options
            $this->helpers->upsert_option( 'log_ip', $field_log_ip );
            $message = urlencode( 'Saved!' );
            wp_redirect( admin_url( 'admin.php?page=c4p-settings&tab=general&c4pmessage=' . $message . '&c4pmessageType=success' ) );
        }
    }

    public function custom_404_pro_admin_init() {
        global $wpdb;
        if(current_user_can('administrator')) {
            if ( array_key_exists( 'action', $_REQUEST ) ) {
                $action = sanitize_text_field($_REQUEST['action']);
                if ( $action === 'c4p-logs--delete' ) {
                    if ( array_key_exists( 'path', $_REQUEST ) ) {
                        $this->helpers->delete_logs( sanitize_text_field($_REQUEST['path']) );
                        $message = urlencode( 'Log(s) successfully deleted!' );
                        wp_redirect( admin_url( 'admin.php?page=c4p-main&c4pmessage=' . $message . '&c4pmessageType=success' ) );
                    } else {
                        $message = urlencode( 'Please select a few logs to delete and try again.' );
                        wp_redirect( admin_url( 'admin.php?page=c4p-main&c4pmessage=' . $message . '&c4pmessageType=warning' ) );
                    }
                } elseif ( $action === 'c4p-logs--delete-all' ) {
                    $this->helpers->delete_logs( 'all' );
                    $message = urlencode( 'All Logs successfully deleted!' );
                    wp_redirect( admin_url( 'admin.php?page=c4p-main&c4pmessage=' . $message . '&c4pmessageType=success' ) );
                } elseif ( $action === 'c4p-logs--export-csv' ) {
                    $this->helpers->export_logs_csv();
                }
            }
        }
    }

    public function custom_404_pro_redirect() {
        global $wpdb;
        if ( is_404() ) {
            $sql                     = 'SELECT * FROM ' . $this->helpers->table_options;
            $sql_result              = $wpdb->get_results( $sql );
            $row_mode                = $sql_result[0];
            $row_mode_page           = $sql_result[1];
            $row_mode_url            = $sql_result[2];
            $row_send_email          = $sql_result[3];
            $row_logging_enabled     = $sql_result[4];
            $row_redirect_error_code = $sql_result[5];
            if ( $row_logging_enabled->value ) {
                self::custom_404_pro_log( $row_send_email->value );
            }
            if ( $row_mode->value === 'page' ) {
                $page = get_post( $row_mode_page->value );
                wp_redirect( $page->guid, $row_redirect_error_code->value );
            } elseif ( $row_mode->value === 'url' ) {
                wp_redirect( $row_mode_url->value, $row_redirect_error_code->value );
            }
        }
    }

    private function custom_404_pro_log( $is_email ) {
        global $wpdb;
        if ( ! $this->helpers->is_option( 'log_ip' ) ) {
            $this->helpers->insert_option( 'log_ip', true );
        }
        if ( empty( $this->helpers->get_option( 'log_ip' ) ) ) {
            $ip = 'N/A';
        } else {
            if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
                $ip = $_SERVER['HTTP_CLIENT_IP'];
            } elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } else {
                $ip = $_SERVER['REMOTE_ADDR'];
            }
        }
        $path    = $_SERVER['REQUEST_URI'];
        $referer = '';
        if ( array_key_exists( 'HTTP_REFERER', $_SERVER ) ) {
            $referer = $_SERVER['HTTP_REFERER'];
        }
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        $sql_save   = 'INSERT INTO ' . $this->helpers->table_logs . " (ip, path, referer, user_agent) VALUES ('$ip', '$path', '$referer', '$user_agent')";
        $wpdb->query( $sql_save );
        if ( ! empty( $is_email ) ) {
            self::custom_404_pro_send_mail( $ip, $path, $referer, $user_agent );
        }
    }

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
        $is_sent   = wp_mail(
            $admin_email,
            '404 Error on Site',
            $message,
            $headers
        );
    }

    private function update_mode( $mode, $page, $url ) {
        global $wpdb;
        if(current_user_can('administrator')) {
            $mode_val      = '';
            $mode_page_val = '';
            $mode_url_val  = '';
            switch ( $mode ) {
                case 'page':
                    $mode_val      = 'page';
                    $mode_page_val = $page;
                    $mode_url_val  = '';
                    break;
                case 'url':
                    $mode_val      = 'url';
                    $mode_page_val = '';
                    $mode_url_val  = $url;
                    break;
                case '':
                    $mode_val      = '';
                    $mode_page_val = '';
                    $mode_url_val  = '';
                    break;
            }
            $this->helpers->update_option( 'mode', $mode_val );
            $this->helpers->update_option( 'mode_page', $mode_page_val );
            $this->helpers->update_option( 'mode_url', $mode_url_val );
        }
    }

    // Not needed for now, commenting
    // public function custom_404_pro_upgrader( $upgrader_object, $options ) {
    //     global $wpdb;
    //     if(current_user_can('administrator')) {
    //         if ( $options['action'] === 'update' && $options['type'] === 'plugin' ) {
    //             if ( ! empty( get_option( 'c4p_mode' ) ) ) {
    //                 $mode = get_option( 'c4p_mode' );
    //                 $page = get_option( 'c4p_selected_page' );
    //                 $url  = get_option( 'c4p_selected_url' );
    //                 self::update_mode( $mode, $page, $url );
    //                 delete_option( 'c4p_mode' );
    //                 delete_option( 'c4p_selected_page' );
    //                 delete_option( 'c4p_selected_url' );
    //             }
    //             // When new features are requested by customers, they usually get a new option.
    //             // This is where we add new option keys when customers upgrade the plugin.
    //             $this->helpers->upsert_option( 'log_ip', true );
    //         }
    //     }
    //     // TODO: Migrate old logs
    // }
}
