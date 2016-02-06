<div class="wrap">
	<div class="update-nag c4p-update-nag">
		<p><b>Note:</b> Global Redirect Settings apply to all logs. If however, a log has a custom redirect, these
			settings will be ignored.</p>
	</div>
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
				Saved <b>Custom URL</b> as a 404 page. <a href="<?php echo $selected_url; ?>" target="blank">Check it
					out</a>.
			</p>
		</div>
	<?php endif; ?>
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
							<option
								value="<?php echo $page->ID; ?>" <?php echo ( $page->ID == $selected_page->ID ) ? "selected" : "" ?>>
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
			<input type='hidden' id="mode" name="mode" value="<?php echo $mode; ?>"/>
			<input type='hidden' name='action' value='select-page-form'/>
			<input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
		</p>
	</form>
	<p class="description">
		<b>Note: </b>To revert back to the default setting, please choose <b>None</b> from the list and <b>Save
			Changes</b>.
	</p>
</div>
