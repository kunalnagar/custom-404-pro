<?php

$active_tab = ( ! isset( $_GET['tab'] ) ) ? 'global-redirect' : $_GET['tab'];

?>

<div class="wrap">
    <h2>Settings</h2>
    <h2 class="nav-tab-wrapper">
        <a href="?page=c4p-settings&tab=global-redirect" class="nav-tab <?php echo $active_tab === 'global-redirect' ? 'nav-tab-active' : ''; ?>">
            Redirect
        </a>
        <a href="?page=c4p-settings&tab=general" class="nav-tab <?php echo $active_tab === 'general' ? 'nav-tab-active' : ''; ?>">
            General
        </a>
    </h2>
</div>

<?php

switch ( $active_tab ) {
    case 'global-redirect':
        include 'settings-global-redirect.php';
        break;
    case 'general':
        include 'settings-general.php';
        break;
}
?>
