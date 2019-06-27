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
				<th>Mode</th>
				<td>
					<select id="c4p_mode" name="mode">
						<option value="">None</option>
						<option value="page" <?php echo ( $mode == 'page' ) ? 'selected' : ''; ?>>
							WordPress Page
						</option>
						<option value="url" <?php echo ( $mode == 'url' ) ? 'selected' : ''; ?>>
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
		<?php foreach ( $pages as $page ) : ?>
							<option value="<?php echo $page->ID; ?>" <?php echo ( $page->ID == $mode_page ) ? 'selected' : ''; ?>>
			<?php echo $page->post_title; ?>
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
					<input id="mode_url" name="mode_url" type="url" class="regular-text" value="<?php echo $mode_url; ?>" autocomplete="off" <?php echo ( ! empty( $mode_url ) ) ? 'required = "required"' : ''; ?>>
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
            <?php wp_nonce_field("form-settings-global-redirect", "form-settings-global-redirect"); ?>
		</p>
	</form>
	<p class="description">
		<b>Note: </b>To revert back to the default setting, please choose <b>None</b> from the list and <b>Save
			Changes</b>.
	</p>
</div>
