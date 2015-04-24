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
// Get All Pubished Pages
$args = array(
'post_type' => 'page',
'post_status' => 'publish'
);
$pages = get_pages( $args );
// Get Selected Page
$mode = get_option( 'c4p_mode' );
$selected_page = maybe_unserialize( get_option( 'c4p_selected_page' ) );
$selected_url = get_option( 'c4p_selected_url' );
// Get Plugin Data
$plugin_main_file = dirname( dirname( dirname( __FILE__ ) ) ) . '/custom-404-pro.php';
$plugin_data = get_plugin_data( $plugin_main_file );
$active_tab = ( !isset( $_GET['tab'] ) ) ? 'c4p-general' : $_GET['tab'];
?>
<div class="wrap">
    <h2>Welcome to Custom 404 Pro</h2>
    <h2 class="nav-tab-wrapper">
    <a href="?page=c4p-main&tab=c4p-general" class="nav-tab <?php echo $active_tab === 'c4p-general' ? 'nav-tab-active': '' ?>">
    General
    </a>
    <a href="?page=c4p-main&tab=c4p-stats" class="nav-tab <?php echo $active_tab === 'c4p-stats' ? 'nav-tab-active': '' ?>">
    Stats
    </a>
    <a href="?page=c4p-main&tab=c4p-settings" class="nav-tab <?php echo $active_tab === 'c4p-settings' ? 'nav-tab-active': '' ?>">
    Settings
    </a>
    </h2>
</div>
<?php
switch ( $active_tab ) {
case 'c4p-general':
include 'custom-404-pro-admin-tab-general.php';
break;
case 'c4p-stats':
include 'custom-404-pro-admin-tab-stats.php';
break;
case 'c4p-settings':
include 'custom-404-pro-admin-tab-settings.php';
break;
}
?>