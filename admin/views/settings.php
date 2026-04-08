<?php

$c4p_tab    = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : '';
$active_tab = empty( $c4p_tab ) ? 'global-redirect' : $c4p_tab;

?>

<div class="wrap">
	<h2>Settings</h2>
	<h2 class="nav-tab-wrapper">
		<a href="?page=c4p-settings&tab=global-redirect" class="nav-tab <?php echo ( 'global-redirect' === $active_tab || '' === $active_tab ) ? 'nav-tab-active' : ''; ?>">
			Redirect
		</a>
		<a href="?page=c4p-settings&tab=general" class="nav-tab <?php echo 'general' === $active_tab ? 'nav-tab-active' : ''; ?>">
			General
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
