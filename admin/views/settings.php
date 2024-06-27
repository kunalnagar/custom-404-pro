<?php

$tab = esc_html($_GET['tab']);
$active_tab = ( ! isset( $tab ) ) ? 'global-redirect' : $tab;

?>

<div class="wrap">
	<h2><?php esc_html_e( 'Settings', 'custom-404-pro' ); ?></h2>
	<h2 class="nav-tab-wrapper">
		<a href="?page=c4p-settings&tab=global-redirect" class="nav-tab <?php echo ($active_tab === 'global-redirect' || $active_tab === '')  ? 'nav-tab-active' : ''; ?>">
			<?php esc_html_e( 'Redirect', 'custom-404-pro' ); ?>
		</a>
		<a href="?page=c4p-settings&tab=general" class="nav-tab <?php echo $active_tab === 'general' ? 'nav-tab-active' : ''; ?>">
			<?php esc_html_e( 'General', 'custom-404-pro' ); ?>
		</a>
	</h2>
</div>

<?php

switch ( $active_tab ) {
    case '':
        include 'settings-global-redirect.php';
        break;
	case 'global-redirect':
		include 'settings-global-redirect.php';
		break;
	case 'general':
		include 'settings-general.php';
		break;
}
?>
