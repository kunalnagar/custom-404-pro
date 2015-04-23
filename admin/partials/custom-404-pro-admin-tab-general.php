<div class="wrap">
	<?php if ( empty( $mode ) && empty( $selected_page ) && empty( $selected_url ) ): ?>
	<div class="error">
		<p><b>No Mode Selected.</b> Default 404 page is in effect.</p>
	</div>
	<?php endif; ?>
	<?php if ( $_GET['message'] == 'updated-page' ): ?>
	<br>
	<div class="updated">
		<p>Saved <b>WordPress Page</b> as a 404 page.</p>
	</div>
	<?php endif; ?>
	<?php if ( $_GET['message'] == 'updated-url' ): ?>
	<br>
	<div class="updated">
		<p>
		Saved <b>Custom URL</b> as a 404 page. <a href="<?php echo $selected_url; ?>" target="blank">Check it out</a>.
		</p>
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
								<th>Mode</th>
								<td>
									<select id="c4p_mode" name="c4p_mode">
										<option value="">None</option>
										<option value="page" <?php echo ( $mode == 'page' ) ? "selected" : "" ?>>
											WordPress Page
										</option>
										<option value="url" <?php echo ( $mode == 'url' ) ? "selected" : "" ?>>
											URL
										</option>
									</select>
									<p class="description">
									<b>WordPress Page:</b> Select any WordPress page as a 404 page.
									</p>
									<p class="description">
									<b>URL:</b> Redirect 404 requests to a specific URL
									</p>
								</td>
							</tr>
							<tr id="c4p_page" class="select-page">
								<th>Select a Page</th>
								<td>
									<select name="c4p_page">
										<option value="">None (Default 404 Page)</option>
										<?php foreach ( $pages as $page ): ?>
										<option value="<?php echo $page->ID; ?>" <?php echo ( $page->ID == $selected_page->ID ) ? "selected" : "" ?>>
											<?php echo $page->post_title; ?>
										</option>
										<?php endforeach; ?>
									</select>
									<p class="description">
									The Default 404 Page will be replaced by the page you choose in this list.
									</p>
								</td>
							</tr>
							<tr id="c4p_url" class="select-url">
								<th>Enter a URL</th>
								<td>
									<input name="c4p_url" type="text" class="regular-text" value="<?php echo $selected_url; ?>">
									<p class="description">
									For e.g.: http://google.com
									</p>
								</td>
							</tr>
						</tbody>
					</table>
					<p class="submit">
					<input type='hidden' id="mode" name="mode" value="<?php echo $mode; ?>" />
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
							How many <a href="https://wordpress.org/support/view/plugin-reviews/custom-404-pro" target="blank">stars</a> would you give us?
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
