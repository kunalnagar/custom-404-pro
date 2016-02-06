<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://kunalnagar.in
 * @since      1.0.0
 *
 * @package    Custom_404_Pro
 * @subpackage Custom_404_Pro/admin/partials
 */

/* Get All Pubished Pages */
$args  = array(
	'post_type'   => 'page',
	'post_status' => 'publish'
);
$pages = get_pages( $args );

/* Get Selected Page */
$mode          = get_option( 'c4p_mode' );
$selected_page = maybe_unserialize( get_option( 'c4p_selected_page' ) );
$selected_url  = get_option( 'c4p_selected_url' );

// Get Plugin Data
$plugin_main_file = dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/custom-404-pro.php';
$plugin_data      = get_plugin_data( $plugin_main_file );
$active_tab       = ( ! isset( $_GET['tab'] ) ) ? 'settings-global-redirect' : $_GET['tab'];
?>

<div class="wrap">
	<h2>Custom 404 Pro Settings</h2><br>
	<h2 class="nav-tab-wrapper">
		<a href="?page=c4p-main&tab=settings-global-redirect"
		   class="nav-tab <?php echo $active_tab === 'settings-global-redirect' ? 'nav-tab-active' : '' ?>">
			Global Redirect
		</a>
		<a href="?page=c4p-main&tab=settings-general"
		   class="nav-tab <?php echo $active_tab === 'settings-general' ? 'nav-tab-active' : '' ?>">
			General
		</a>
	</h2>
</div>

<?php
switch ( $active_tab ) {
	case 'settings-global-redirect':
		include 'custom-404-pro-admin-settings-global-redirect.php';
		break;
	case 'settings-general':
		include 'custom-404-pro-admin-settings-general.php';
		break;
}
?>
