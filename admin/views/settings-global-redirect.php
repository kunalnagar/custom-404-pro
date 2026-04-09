<?php
/**
 * Global redirect settings view.
 *
 * @package Custom_404_Pro
 */

global $wpdb;
$args               = array(
	'post_type'   => 'page',
	'post_status' => 'publish',
);
$wp_pages           = get_pages( $args );
$sql_mode           = $wpdb->prepare( 'SELECT value FROM ' . $wpdb->prefix . 'custom_404_pro_options WHERE name = %s', 'mode' ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
$redirect_mode      = $wpdb->get_var( $sql_mode ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
$redirect_mode_page = '';
$redirect_mode_url  = '';
if ( 'page' === $redirect_mode ) {
	$sql_mode_page      = $wpdb->prepare( 'SELECT value FROM ' . $wpdb->prefix . 'custom_404_pro_options WHERE name = %s', 'mode_page' ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	$redirect_mode_page = $wpdb->get_var( $sql_mode_page ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
} elseif ( 'url' === $redirect_mode ) {
	$sql_mode_url      = $wpdb->prepare( 'SELECT value FROM ' . $wpdb->prefix . 'custom_404_pro_options WHERE name = %s', 'mode_url' ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	$redirect_mode_url = $wpdb->get_var( $sql_mode_url ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
}
?>
<div class="wrap">
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<table class="form-table">
			<tbody>
			<tr>
				<th>Mode</th>
				<td>
					<select id="c4p_mode" name="mode">
						<option value="">None</option>
						<option value="page" <?php echo ( 'page' === $redirect_mode ) ? 'selected' : ''; ?>>
							WordPress Page
						</option>
						<option value="url" <?php echo ( 'url' === $redirect_mode ) ? 'selected' : ''; ?>>
							URL
						</option>
					</select>
					<p class="description">
						<b>WordPress Page:</b> Select any WordPress page as a redirect page.
					</p>
					<p class="description">
						<b>URL:</b> Redirect chosen error requests to a specific URL
					</p>
				</td>
			</tr>
			<tr id="c4p_page" class="select-page">
				<th>Select a Page</th>
				<td>
					<select name="mode_page">
						<option value="">None (Default Error Page)</option>
		<?php foreach ( $wp_pages as $wp_page ) : ?>
							<option value="<?php echo esc_attr( $wp_page->ID ); ?>" <?php echo ( $wp_page->ID === (int) $redirect_mode_page ) ? 'selected' : ''; ?>>
			<?php echo esc_html( $wp_page->post_title ); ?>
							</option>
		<?php endforeach; ?>
					</select>
					<p class="description">
						The Default error page will be replaced by the page you choose in this list.
					</p>
				</td>
			</tr>
			<tr id="c4p_url" class="select-url">
				<th>Enter a URL</th>
				<td>
					<input id="mode_url" name="mode_url" type="url" class="regular-text" value="<?php echo esc_url( $redirect_mode_url ); ?>" autocomplete="off" <?php echo ( ! empty( $redirect_mode_url ) ) ? 'required = "required"' : ''; ?>>
					<p class="description">
						Enter a valid URL, for e.g. https://google.com
					</p>
				</td>
			</tr>
			</tbody>
		</table>
		<p class="submit">
			<input type="hidden" name="action" value="form-settings-global-redirect"/>
			<input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
			<?php wp_nonce_field( 'form-settings-global-redirect', 'form-settings-global-redirect' ); ?>
		</p>
	</form>
	<p class="description">
		<b>Note: </b>To revert back to the default setting, please choose <b>None</b> from the list and <b>Save
			Changes</b>.
	</p>
</div>
