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
class Custom_404_Pro_Admin
{
    
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
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }
    
    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        
        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Custom_404_Pro_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Custom_404_Pro_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/custom-404-pro-admin.css', array(), $this->version, 'all');
    }
    
    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        
        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Custom_404_Pro_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Custom_404_Pro_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/custom-404-pro-admin.js', array('jquery'), $this->version, false);
    }
    
    /**
     * Create the Plugin Admin Menu
     *
     * @since    1.0.0
     */
    public function main_admin_menu() {
        add_submenu_page('options-general.php', 'Custom 404 Pro', 'Custom 404 Pro', 'manage_options', 'c4p-main', array($this, 'main_admin_menu_display'));
    }
    
    /**
     * Plugin Main View
     *
     * @since    1.0.0
     */
    public function main_admin_menu_display() {
        include 'partials/custom-404-pro-admin-display.php';
    }
    
    /**
     * Save the Selected 404 Page
     *
     * @since    1.0.0
     */
    public function select_page_submit() {
        if (isset($_POST['mode']) && !empty($_POST['mode'])) {
            $mode = $_POST['mode'];
            switch ($mode) {
                case 'page':
                    $page = get_post($_POST['c4p_page']);
                    update_option('c4p_mode', 'page');
                    update_option('c4p_selected_page', maybe_serialize($page));
                    update_option('c4p_selected_url', '');
                    wp_redirect(admin_url('admin.php?page=c4p-main&message=updated-page'));
                    break;

                case 'url':
                    $url = $_POST['c4p_url'];
                    update_option('c4p_mode', 'url');
                    update_option('c4p_selected_url', $url);
                    update_option('c4p_selected_page', '');
                    wp_redirect(admin_url('admin.php?page=c4p-main&message=updated-url'));
                    break;
            }
        } 
        else {
            update_option('c4p_mode', '');
            update_option('c4p_selected_url', '');
            update_option('c4p_selected_page', '');
            wp_redirect(admin_url('admin.php?page=c4p-main'));
        }
    }
    
    /**
     * Custom 404 Hook to redirect users to chosen 404 Page
     *
     * @since    1.0.0
     */
    public function custom_404() {
        if (is_404()) {
            $c4p_404_data = array();
            $temp_data = get_option('c4p_404_data');
            if (!empty($temp_data)) {
                $c4p_404_data = maybe_unserialize(get_option('c4p_404_data'));
            }
            if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
                $ip = $_SERVER['HTTP_CLIENT_IP'];
            } 
            else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } 
            else {
                $ip = $_SERVER['REMOTE_ADDR'];
            }
            $data = array('ip' => $ip, 'path' => $_SERVER['REQUEST_URI'], 'referrer' => $_SERVER['HTTP_REFERER'], 'time' => current_time('timestamp'));
            array_push($c4p_404_data, $data);
            update_option('c4p_404_data', maybe_serialize($c4p_404_data));
            
            $is_selected_page = get_option('c4p_selected_page');
            $url = get_option('c4p_selected_url');
            if (!empty($is_selected_page)) {
                $selected_page = maybe_unserialize(get_option('c4p_selected_page'));
                wp_redirect(site_url() . '/' . $selected_page->post_name);
            } 
            else if (!empty($url)) {
                wp_redirect($url);
            }
        }
    }
}
