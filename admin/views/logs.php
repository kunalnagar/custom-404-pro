<?php

require_once __DIR__ . '../../LogsClass.php';

$logs_table = new LogsClass();
$logs_table->prepare_items();

?>

<div class="wrap">
	<h2><?php esc_html_e( 'Logs', 'custom-404-pro' ); ?></h2>
	<form id="form_logs" method="GET">
		<!-- For plugins, we also need to ensure that the form posts back to our current page -->
		<input type="hidden" name="page" value="<?php echo esc_attr($_REQUEST['page']); ?>" />
        <?php wp_nonce_field("form-logs-options", "form-logs-options"); ?>
		<!-- Now we can render the completed list table -->
		<p class="search-box">
			<label class="screen-reader-text" for="search_id-search-input"><?php esc_html_e( 'Search', 'custom-404-pro' ); ?></label>
			<input id="search_id-search-input" type="text" name="s" value="<?php if ( array_key_exists( 's', $_GET ) ) { echo esc_attr($_GET['s']); } ?>" autocomplete="off" />
			<input id="search-submit" class="button" type="submit" name="" value="<?php esc_attr_e( 'Search', 'custom-404-pro' ); ?>" />
		</p>
		<?php $logs_table->display(); ?>
	</form>
</div>
