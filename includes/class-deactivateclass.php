<?php
/**
 * Handles plugin deactivation.
 *
 * @package Custom_404_Pro
 */

/**
 * Deactivation class.
 */
class DeactivateClass {

	/**
	 * Runs on plugin deactivation.
	 *
	 * Unschedules the daily log-pruning cron event. On multisite, this fires
	 * for the main site only; per-site events on sub-sites will expire naturally.
	 *
	 * @since 3.14.0
	 */
	public static function deactivate() {
		$timestamp = wp_next_scheduled( 'custom_404_pro_prune_logs' );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, 'custom_404_pro_prune_logs' );
		}
	}
}
