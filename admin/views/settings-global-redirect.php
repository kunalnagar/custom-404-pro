<?php
global $wpdb;
$args      = array(
	'post_type'   => 'page',
	'post_status' => 'publish',
);
$pages     = get_pages( $args );
$sql_mode  = 'SELECT value FROM ' . $wpdb->prefix . 'custom_404_pro_options' . ' WHERE name="mode"';
$mode      = $wpdb->get_var( $sql_mode );
$mode_page = '';
$mode_url  = '';
if ( $mode === 'page' ) {
	$sql_mode_page = 'SELECT value FROM ' . $wpdb->prefix . 'custom_404_pro_options' . ' WHERE name="mode_page"';
	$mode_page     = $wpdb->get_var( $sql_mode_page );
} elseif ( $mode === 'url' ) {
	$sql_mode_url = 'SELECT value FROM ' . $wpdb->prefix . 'custom_404_pro_options' . ' WHERE name="mode_url"';
	$mode_url     = $wpdb->get_var( $sql_mode_url );
}
?>
<div class="wrap">
	<form method="post" action="<?php echo get_admin_url() . 'admin-post.php'; ?>">
		<table class="form-table">
			<tbody>
			<tr>
				<th><?php esc_html_e( 'Mode', 'custom-404-pro' ); ?></th>
				<td>
					<select id="c4p_mode" name="mode">
						<option value=""><?php esc_html_e( 'None', 'custom-404-pro' ); ?></option>
						<option value="page" <?php echo ( $mode == 'page' ) ? 'selected' : ''; ?>>
							<?php esc_html_e( 'WordPress Page', 'custom-404-pro' ); ?>
						</option>
						<option value="url" <?php echo ( $mode == 'url' ) ? 'selected' : ''; ?>>
							<?php esc_html_e( 'URL', 'custom-404-pro' ); ?>
						</option>
					</select>
					<p class="description">
						<?php printf(esc_html__( '%1$sWordPress Page:%2$s Select any WordPress page as a redirect page.', 'custom-404-pro' ),'<b>','</b>'); ?>
					</p>
					<p class="description">
						<?php printf(esc_html__( '%1$sURL:%2$s Redirect chosen error requests to a specific URL.', 'custom-404-pro' ),'<b>','</b>'); ?>
					</p>
				</td>
			</tr>
			<tr id="c4p_page" class="select-page">
				<th><?php esc_html_e( 'Select a Page', 'custom-404-pro' ); ?></th>
				<td>
					<select name="mode_page">
						<option value=""><?php esc_html_e( 'None (Default Error Page)', 'custom-404-pro' ); ?></option>
		<?php foreach ( $pages as $page ) : ?>
							<option value="<?php echo $page->ID; ?>" <?php echo ( $page->ID == $mode_page ) ? 'selected' : ''; ?>>
			<?php echo $page->post_title; ?>
							</option>
		<?php endforeach; ?>
					</select>
					<p class="description">
						<?php esc_html_e( 'The Default error page will be replaced by the page you choose in this list.', 'custom-404-pro' ); ?>
					</p>
				</td>
			</tr>
			<tr id="c4p_url" class="select-url">
				<th><?php esc_html_e( 'Enter a URL', 'custom-404-pro' ); ?></th>
				<td>
					<input id="mode_url" name="mode_url" type="url" class="regular-text" value="<?php echo $mode_url; ?>" autocomplete="off" <?php echo ( ! empty( $mode_url ) ) ? 'required = "required"' : ''; ?>>
					<p class="description">
						<?php esc_html_e( 'Enter a valid URL, for e.g. https://google.com', 'custom-404-pro' ); ?>
					</p>
				</td>
			</tr>
			</tbody>
		</table>
		<p class="submit">
			<input type="hidden" name="action" value="form-settings-global-redirect"/>
			<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_attr_e( 'Save Changes', 'custom-404-pro' ); ?>">
            <?php wp_nonce_field("form-settings-global-redirect", "form-settings-global-redirect"); ?>
		</p>
	</form>
	<p class="description">
		<?php printf(esc_html__( '%1$sNote:%2$s To revert back to the default setting, please choose %1$sNone%2$s from the list and %1$sSave Changes%2$s.', 'custom-404-pro' ),'<b>','</b>'); ?>
	</p>
</div>
