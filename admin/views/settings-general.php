<?php
global $wpdb;
$helpers                 = Helpers::singleton();
$sql                     = 'SELECT * FROM ' . $helpers->table_options;
$result                  = $wpdb->get_results( $sql );
$row_send_email          = $result[3];
$row_logging_enabled     = $result[4];
$row_redirect_error_code = $result[5];
if ( array_key_exists( 6, $result ) ) {
	$row_log_ip = $result[6];
} else {
	$row_log_ip        = new stdClass();
	$row_log_ip->value = true;
}
?>
<div class="wrap">
    <form method="post" action="<?php echo get_admin_url() . 'admin-post.php'; ?>">
		<table class="form-table">
			<tbody>
			<tr>
				<th>Email</th>
				<td>
					<input type="checkbox" id="c4p_log_email" name="send_email" <?php echo $row_send_email->value == true ? 'checked' : ''; ?> />
					<p class="description">
						If you check this, <b>and logging is enabled</b>, an email will be sent on every error log on the admin's email account. If you're just starting out, it is recommended you uncheck this. Enable it based on your error volume to avoid flooding of your email inbox.
					</p>
				</td>
			</tr>
			<tr>
				<th>Logging Status</th>
				<td>
					<select name="logging_enabled">
						<option value="enabled" <?php echo $row_logging_enabled->value == true ? 'selected' : ''; ?>>
							Enabled
						</option>
						<option value="disabled" <?php echo $row_logging_enabled->value == false ? 'selected' : ''; ?>>
							Disabled
						</option>
					</select>
					<p class="description">
						If logging status is <b>Enabled</b>, the plugin will capture logs. If the logging status is <b>Disabled</b>, the plugin will stop capturing logs. Please note that your previous logs will <b>NOT</b> be deleted. You may do so from the <b>Logs</b> page.
					</p>
				</td>
			</tr>
			<tr>
				<th>Log IP</th>
				<td>
					<input type="checkbox" id="c4p_log_ip" name="log_ip" <?php echo $row_log_ip->value == true ? 'checked' : ''; ?> />
					<p class="description">
						By default, the IP address of the 404 user agent is captured. If you would like to disable this for privacy reasons, please uncheck this box. When no IP is recorded, it will appear as <b>N/A</b> in the Logs Table as well as the email.
					</p>
				</td>
			</tr>
			<tr>
				<th>Redirect Code</th>
				<td>
					<select name="redirect_error_code">
						<option value="301" <?php echo $row_redirect_error_code->value == 301 ? 'selected' : ''; ?>>301
						</option>
						<option value="302" <?php echo $row_redirect_error_code->value == 302 ? 'selected' : ''; ?>>302
						</option>
						<option value="307" <?php echo $row_redirect_error_code->value == 307 ? 'selected' : ''; ?>>307
						</option>
						<option value="308" <?php echo $row_redirect_error_code->value == 308 ? 'selected' : ''; ?>>308
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
            <?php wp_nonce_field("form-settings-general", "form-settings-general"); ?>
		</p>
	</form>
</div>
