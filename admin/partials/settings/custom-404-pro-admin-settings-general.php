<div class="wrap">
	<?php if ( $_GET['message'] == 'settings_general_form-updated' ): ?>
		<br>
		<div class="updated">
			<p>Saved!</p>
		</div>
	<?php endif; ?>
	<form method="post" action="<?php echo get_admin_url() . 'admin-post.php'; ?>">
		<table class="form-table">
			<tbody>
			<tr>
				<th>Email</th>
				<td>
					<input type="checkbox" id="c4p_log_email"
					       name="c4p_log_email" <?php echo get_option( 'c4p_log_email' ) == true ? 'checked' : '' ?> />
					<p class="description">
						If you check this, an email will be sent on every 404 log on the admin's email account.
					</p>
				</td>
			</tr>
			<tr>
				<th>Clear Logs</th>
				<td>
					<input type="button" name="clear_logs" id="clear_logs" class="button button-default" value="Clear Logs">
					<p class="clear-logs-description description">
						Clearing Logs: <span class="total-logs"></span>
					</p>
				</td>
			</tr>
			<tr>
				<th>Logging Status</th>
				<td>
					<select name="c4p_logging_status">
						<option
							value="enabled" <?php echo get_option( 'c4p_logging_status' ) == 'enabled' ? 'selected' : '' ?>>
							Enabled
						</option>
						<option
							value="disabled" <?php echo get_option( 'c4p_logging_status' ) == 'disabled' ? 'selected' : '' ?>>
							Disabled
						</option>
					</select>
					<p class="description">
						Enable/Disable Logging
					</p>
				</td>
			</tr>
			<tr>
				<th>Error Type</th>
				<td>
					<select name="c4p_log_type">
						<option value="302" <?php echo get_option( 'c4p_log_type' ) == 302 ? 'selected' : '' ?>>302
						</option>
						<option value="301" <?php echo get_option( 'c4p_log_type' ) == 301 ? 'selected' : '' ?>>301
						</option>
						<option value="404" <?php echo get_option( 'c4p_log_type' ) == 404 ? 'selected' : '' ?>>404
						</option>
					</select>
					<p class="description">
						Log Type: 302 (Default)
					</p>
				</td>
			</tr>
			</tbody>
		</table>
		<p class="submit">
			<input type='hidden' name='action' value='settings-general-form'/>
			<input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
		</p>
	</form>
</div>
