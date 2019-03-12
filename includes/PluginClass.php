<?php

class PluginClass {

	public function __construct() {
		$this->plugin_admin;
		$this->plugin_public;
		$this->load_dependencies();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	private function load_dependencies() {
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/AdminClass.php';
		$this->plugin_admin = new AdminClass();
		// require_once plugin_dir_path(dirname(__FILE__)) . 'public/PublicClass.php';
		// $this->plugin_public = new PublicClass();
	}

	private function define_admin_hooks() {
		add_action('admin_menu', array($this->plugin_admin, 'create_menu'));
		add_action('admin_enqueue_scripts', array($this->plugin_admin, 'enqueue_scripts'));
		add_action('admin_enqueue_scripts', array($this->plugin_admin, 'enqueue_styles'));
		add_action('admin_post_form-settings-global-redirect', array($this->plugin_admin, 'form_settings_global_redirect'));
		add_action('admin_post_form-settings-general', array($this->plugin_admin, 'form_settings_general'));
		add_action('admin_post_form-reset', array($this->plugin_admin, 'form_reset'));
		add_action('template_redirect', array($this->plugin_admin, 'custom_404_pro_redirect'));
        add_action('upgrader_process_complete', array($this->plugin_admin, 'custom_404_pro_upgrader'));
	}

	private function define_public_hooks() {
		// TODO
	}
}