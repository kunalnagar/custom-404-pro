<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://kunalnagar.in
 * @since      1.0.0
 *
 * @package    Custom_404_Pro
 * @subpackage Custom_404_Pro/admin/partials
 */

// Get All Pubished Pages
$args = array(
	'post_type' => 'page',
	'post_status' => 'publish'
);
$pages = get_pages($args);

// Get Selected Page
$selected_page = maybe_unserialize(get_option('c4p_selected_page'));

// Get Plugin Data
$plugin_main_file = dirname(dirname(dirname(__FILE__))) . '/custom-404-pro.php';
$plugin_data = get_plugin_data($plugin_main_file);
?>

<div class="wrap">
	<h2>Welcome to Custom 404 Pro</h2><hr>
	<?php if($_GET['message'] == 'updated'): ?>
	<br>
	<div class="updated">
        <p>Selected page has been saved as a 404 page!</p>
    </div>
	<?php endif; ?>
	<div id="poststuff">
		<div id="post-body" class="metabox-holder columns-2">
			<!-- Main Form -->
			<div id="post-body-content">
				<form method="post" action="<?php echo get_admin_url() . 'admin-post.php'; ?>">
					<table class="form-table">
						<tbody>
							<tr>
								<th>Select a Page</th>
								<td>
									<select name="selected_page">
										<option value="">None (Default 404 Page)</option>
										<?php foreach($pages as $page): ?>
											<?php if($page->ID == $selected_page->ID): ?>
											<option value="<?php echo $page->ID; ?>" selected>
												<?php echo $page->post_title; ?>
											</option>
											<?php else: ?>
											<option value="<?php echo $page->ID; ?>">
												<?php echo $page->post_title; ?>
											</option>
											<?php endif; ?>
										<?php endforeach; ?>
									</select>
									<p class="description">
										The Default 404 Page will be replaced by the page you choose in this list.
									</p>
								</td>
							</tr>
						</tbody>
					</table>
					<p class="submit">
						<input type='hidden' name='action' value='select-page-form' />
						<input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
					</p>
				</form>
				<p class="description">
					<b>Note: </b>To revert back to the default setting, please choose <b>None</b> from the list and <b>Save Changes</b>.
				</p>
			</div>
			<!-- Plugin Meta -->
			<div id="postbox-container-1" class="postbox-container">
				<div class="postbox">
					<h3 class="hndle ui-sortable-handle">Plugin Info</h3>
					<div class="inside">
						<div class="misc-pub-section">
							<label>Name:</label>
							<span>
								<b><?php echo $plugin_data['Title']; ?></b>
							</span>
						</div>
						<div class="misc-pub-section">
							<label>Version:</label>
							<span>
								<b><?php echo $plugin_data['Version']; ?></b>
							</span>
						</div>
						<div class="misc-pub-section">
							<label>Author:</label>
							<span>
								<b><?php echo $plugin_data['Author']; ?></b>
							</span>
						</div>
						<div class="misc-pub-section">
							<label>Email:</label>
							<span>
								<b><a href="mailto:knlnagar@gmail.com">knlnagar@gmail.com</a></b>
							</span>
						</div>
						<div class="misc-pub-section">
							<label>Location:</label>
							<span>
								<b>Settings > Custom 404 Pro</b>
							</span>
						</div>
					</div>
				</div>
				<div class="postbox">
					<h3 class="hndle ui-sortable-handle">Issues</h3>
					<div class="inside">
						<div class="misc-pub-section">
							For any issues regarding this plugin, please open an issue on <a href="https://github.com/kunalnagar/custom-404-pro/issues" target="blank">Github</a>.
						</div>
					</div>
				</div>
				<div class="postbox">
					<h3 class="hndle ui-sortable-handle">Rate</h3>
					<div class="inside">
						<div class="misc-pub-section">
							How many <a href="https://wordpress.org/support/view/plugin-reviews/custom-404-pro">stars</a> would you give us?
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
