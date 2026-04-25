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
$max_days    = isset( $options['log_retention_days'] ) ? (int) $options['log_retention_days'] : 0;

?>

<div class="wrap">
	<h2><?php esc_html_e( 'Logs', 'custom-404-pro' ); ?></h2>

	<p>
		<?php
		echo esc_html(
			sprintf(
				/* translators: %d: number of log rows */
				__( 'Total log entries: %d', 'custom-404-pro' ),
				(int) $total_count
			)
		);
		if ( $max_count > 0 ) {
			echo ' / ' . (int) $max_count;
		}
		?>
	</p>

	<?php if ( $max_count > 0 && $total_count >= $max_count * 0.9 ) : ?>
		<div class="notice notice-warning inline">
			<p>
				<?php
				echo esc_html(
					sprintf(
						/* translators: 1: current log count, 2: max log count */
						__( 'Log table is at %1$d of %2$d rows (90%% threshold reached). Consider pruning or raising the Max Log Count setting.', 'custom-404-pro' ),
						(int) $total_count,
						(int) $max_count
					)
				);
				?>
			</p>
		</div>
	<?php endif; ?>

	<?php if ( $max_count > 0 || $max_days > 0 ) : ?>
	<p>
		<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=c4p-main&action=c4p-logs--prune' ), 'c4p-logs--prune' ) ); ?>" class="button">
			<?php esc_html_e( 'Prune Logs Now', 'custom-404-pro' ); ?>
		</a>
	</p>
	<?php endif; ?>

	<form id="form_logs" method="GET">
		<!-- For plugins, we also need to ensure that the form posts back to our current page -->
		<input type="hidden" name="page" value="<?php echo esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['page'] ?? '' ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>" />
		<!-- Now we can render the completed list table -->
		<p class="search-box">
			<label class="screen-reader-text" for="search_id-search-input"><?php esc_html_e( 'Search', 'custom-404-pro' ); ?></label>
			<input id="search_id-search-input" type="text" name="s" value="
			<?php
			if ( array_key_exists( 's', $_GET ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				echo esc_attr( sanitize_text_field( wp_unslash( $_GET['s'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			}
			?>
			" autocomplete="off" />
			<input id="search-submit" class="button" type="submit" name="" value="<?php echo esc_attr__( 'Search', 'custom-404-pro' ); ?>" />
		</p>
		<?php $logs_table->display(); ?>
	</form>
</div>
