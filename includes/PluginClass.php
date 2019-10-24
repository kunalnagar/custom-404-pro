<?php

class PluginClass {

	public function __construct() {
		$this->plugin_admin = '';
		$this->load_dependencies();
		$this->define_admin_hooks();
	}

	private function load_dependencies() {
		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/AdminClass.php';
		$this->plugin_admin = new AdminClass();
	}

	private function define_admin_hooks() {
		// Core action hooks
        add_action( 'admin_menu', array( $this->plugin_admin, 'create_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this->plugin_admin, 'enqueue_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this->plugin_admin, 'enqueue_styles' ) );
		add_action( 'admin_init', array( $this->plugin_admin, 'custom_404_pro_admin_init' ) );
		add_action( 'template_redirect', array( $this->plugin_admin, 'custom_404_pro_redirect' ) );
		// add_action( 'upgrader_process_complete', array( $this->plugin_admin, 'custom_404_pro_upgrader' ), 10, 2 );
		add_action( 'admin_notices', array( $this->plugin_admin, 'custom_404_pro_notices' ) );

		// Custom hooks
		add_action( 'admin_post_form-settings-global-redirect', array( $this->plugin_admin, 'form_settings_global_redirect' ) );
		add_action( 'admin_post_form-settings-general', array( $this->plugin_admin, 'form_settings_general' ) );
	}
}
