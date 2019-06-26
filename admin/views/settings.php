<?php

$tab = esc_html($_GET['tab']);
$active_tab = ( ! isset( $tab ) ) ? 'global-redirect' : $tab;

?>

<div class="wrap">
	<h2>Settings</h2>
	<h2 class="nav-tab-wrapper">
		<a href="?page=c4p-settings&tab=global-redirect" class="nav-tab <?php echo ($active_tab === 'global-redirect' || $active_tab === '')  ? 'nav-tab-active' : ''; ?>">
			Redirect
		</a>
		<a href="?page=c4p-settings&tab=general" class="nav-tab <?php echo $active_tab === 'general' ? 'nav-tab-active' : ''; ?>">
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
