<?php
global $wpdb;
$sql = "SELECT * FROM " . $wpdb->prefix . "custom_404_pro_options";
$result = $wpdb->get_results($sql);
// echo "<pre>";
// print_r($result);
// echo "</pre>";
$row_send_email = $result[3];
$row_logging_enabled = $result[4];
$row_redirect_error_code = $result[5];
?>
<div class="wrap">
	<?php if($_GET["message"] === "updated"): ?>
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
					<input type="checkbox" id="c4p_log_email" name="send_email" <?php echo $row_send_email->value == true ? "checked" : "" ?> />
					<p class="description">
						If you check this, an email will be sent on every error log on the admin's email account. If you're just starting out, it is recommended you uncheck this. Enable it based on your error volume to avoid flooding of your email inbox.
					</p>
				</td>
			</tr>
			<!-- <tr>
				<th>Clear Logs</th>
				<td>
					<input type="button" name="clear_logs" id="clear_logs" class="button button-default" value="Clear Logs">
					<?php if($row_clear_logs->created !== $row_clear_logs->updated): ?>
					<p class="description description--last-cleared">
						Last cleared: <?php echo $row_clear_logs->updated; ?>
					</p>
					<?php endif; ?>
					<p class="description">
						<b>Note:</b> This will clear ALL logs created by the plugin. It may take a while depending on the number of logs in the database.
					</p>
				</td>
			</tr> -->
			<tr>
				<th>Logging Status</th>
				<td>
					<select name="logging_enabled">
						<option value="enabled" <?php echo $row_logging_enabled->value == true ? "selected" : "" ?>>
							Enabled
						</option>
						<option value="disabled" <?php echo $row_logging_enabled->value == false ? "selected" : "" ?>>
							Disabled
						</option>
					</select>
					<p class="description">
						If logging status is <b>Enabled</b>, the plugin will capture logs. If the logging status is <b>Disabled</b>, the plugin will stop capturing logs. Please note that your previous logs will <b>NOT</b> be deleted. You may do so from the <b>Logs</b> page.
					</p>
				</td>
			</tr>
			<tr>
				<th>Redirect Code</th>
				<td>
					<select name="redirect_error_code">
						<option value="301" <?php echo $row_redirect_error_code->value == 301 ? "selected" : '' ?>>301
						</option>
						<option value="302" <?php echo $row_redirect_error_code->value == 302 ? "selected" : '' ?>>302
						</option>
						<option value="307" <?php echo $row_redirect_error_code->value == 307 ? "selected" : '' ?>>307
						</option>
						<option value="308" <?php echo $row_redirect_error_code->value == 308 ? "selected" : '' ?>>308
						</option>
					</select>
					<p class="description">
						When a 404 occurs and a redirect mode has been set, it will be performed using this status code.
					</p>
				</td>
			</tr>
			</tbody>
		</table>
		<p class="submit">
			<input type="hidden" name="action" value="form-settings-general"/>
			<input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
		</p>
	</form>
</div>