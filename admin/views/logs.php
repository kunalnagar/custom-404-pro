<?php
/**
 * Logs page view.
 *
 * @package Custom_404_Pro
 */

$logs_table  = new LogsClass();
$logs_table->prepare_items();

$helpers     = Helpers::singleton();
$options     = $helpers->get_settings();
$total_count = $helpers->get_logs_count();
$max_count   = isset( $options['log_retention_count'] ) ? (int) $options['log_retention_count'] : 0;

?>

<div class="wrap">
	<h2>Logs</h2>

	<p>
		<?php
		/* translators: %d: number of log rows */
		printf( esc_html__( 'Total log entries: %d', 'custom-404-pro' ), $total_count );
		if ( $max_count > 0 ) {
			echo ' / ' . esc_html( $max_count );
		}
		?>
	</p>

	<?php if ( $max_count > 0 && $total_count >= $max_count * 0.9 ) : ?>
		<div class="notice notice-warning inline">
			<p>
				<?php
				printf(
					/* translators: 1: current log count, 2: max log count */
					esc_html__( 'Log table is at %1$d of %2$d rows (90%% threshold reached). Consider pruning or raising the Max Log Count setting.', 'custom-404-pro' ),
					$total_count,
					$max_count
				);
				?>
			</p>
		</div>
	<?php endif; ?>

	<p>
		<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=c4p-main&action=c4p-logs--prune' ), 'c4p-logs--prune' ) ); ?>" class="button">
			<?php esc_html_e( 'Prune Logs Now', 'custom-404-pro' ); ?>
		</a>
	</p>

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
