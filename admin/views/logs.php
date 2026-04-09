<?php
/**
 * Logs page view.
 *
 * @package Custom_404_Pro
 */

$logs_table = new LogsClass();
$logs_table->prepare_items();

?>

<div class="wrap">
	<h2>Logs</h2>
	<form id="form_logs" method="GET">
		<!-- For plugins, we also need to ensure that the form posts back to our current page -->
		<input type="hidden" name="page" value="<?php echo esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['page'] ?? '' ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>" />
		<!-- Now we can render the completed list table -->
		<p class="search-box">
			<label class="screen-reader-text" for="search_id-search-input">Search</label>
			<input id="search_id-search-input" type="text" name="s" value="
			<?php
			if ( array_key_exists( 's', $_GET ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				echo esc_attr( sanitize_text_field( wp_unslash( $_GET['s'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			}
			?>
			" autocomplete="off" />
			<input id="search-submit" class="button" type="submit" name="" value="Search" />
		</p>
		<?php $logs_table->display(); ?>
	</form>
</div>
