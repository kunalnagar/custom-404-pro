<?php
/**
 * General settings view.
 *
 * @package Custom_404_Pro
 */

$helpers             = Helpers::singleton();
$options             = $helpers->get_settings();
$send_email          = $options['send_email'] ?? false;
$logging_enabled     = $options['logging_enabled'] ?? false;
$redirect_error_code = isset( $options['redirect_error_code'] ) ? (int) $options['redirect_error_code'] : 302;
$log_ip              = $options['log_ip'] ?? true;
$email_cooldown      = isset( $options['email_cooldown'] ) ? (int) $options['email_cooldown'] : 3600;
$log_retention_count = isset( $options['log_retention_count'] ) ? (int) $options['log_retention_count'] : 0;
$log_retention_days  = isset( $options['log_retention_days'] ) ? (int) $options['log_retention_days'] : 0;
?>
<div class="wrap">
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<table class="form-table">
			<tbody>
			<tr>
				<th>Email</th>
				<td>
					<input type="checkbox" id="c4p_log_email" name="send_email" <?php echo (bool) $send_email ? 'checked' : ''; ?> />
					<p class="description">
						If you check this, <b>and logging is enabled</b>, an email will be sent on every error log on the admin's email account. If you're just starting out, it is recommended you uncheck this. Enable it based on your error volume to avoid flooding of your email inbox.
					</p>
				</td>
			</tr>
			<tr>
				<th>Email Notification Cooldown</th>
				<td>
					<select name="email_cooldown">
						<option value="900" <?php echo 900 === $email_cooldown ? 'selected' : ''; ?>>15 minutes</option>
						<option value="1800" <?php echo 1800 === $email_cooldown ? 'selected' : ''; ?>>30 minutes</option>
						<option value="3600" <?php echo 3600 === $email_cooldown ? 'selected' : ''; ?>>1 hour</option>
						<option value="21600" <?php echo 21600 === $email_cooldown ? 'selected' : ''; ?>>6 hours</option>
						<option value="86400" <?php echo 86400 === $email_cooldown ? 'selected' : ''; ?>>24 hours</option>
					</select>
					<p class="description">
						Only applies when <b>Email</b> is enabled. After a notification email is sent, no further emails will be sent for the selected duration. This prevents inbox flooding during bot crawls or traffic spikes.
					</p>
				</td>
			</tr>
			<tr>
				<th>Logging Status</th>
				<td>
					<select name="logging_enabled">
						<option value="enabled" <?php echo (bool) $logging_enabled ? 'selected' : ''; ?>>
							Enabled
						</option>
						<option value="disabled" <?php echo ! (bool) $logging_enabled ? 'selected' : ''; ?>>
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
					<input type="checkbox" id="c4p_log_ip" name="log_ip" <?php echo (bool) $log_ip ? 'checked' : ''; ?> />
					<p class="description">
						By default, the IP address of the 404 user agent is captured. If you would like to disable this for privacy reasons, please uncheck this box. When no IP is recorded, it will appear as <b>N/A</b> in the Logs Table as well as the email.
					</p>
				</td>
			</tr>
			<tr>
				<th>Redirect Code</th>
				<td>
					<select name="redirect_error_code">
						<option value="301" <?php echo 301 === $redirect_error_code ? 'selected' : ''; ?>>301
						</option>
						<option value="302" <?php echo 302 === $redirect_error_code ? 'selected' : ''; ?>>302
						</option>
						<option value="307" <?php echo 307 === $redirect_error_code ? 'selected' : ''; ?>>307
						</option>
						<option value="308" <?php echo 308 === $redirect_error_code ? 'selected' : ''; ?>>308
						</option>
					</select>
					<p class="description">
						When a 404 occurs and a redirect mode has been set, it will be performed using this status code.
					</p>
				</td>
			</tr>
			<tr>
				<th>Max Log Count</th>
				<td>
					<input type="number" name="log_retention_count" value="<?php echo esc_attr( $log_retention_count ); ?>" min="0" step="1" />
					<p class="description">
						Maximum number of log rows to keep. When the table exceeds this limit, the oldest rows are deleted automatically during the daily cleanup. Set to <b>0</b> to disable (no limit).
					</p>
				</td>
			</tr>
			<tr>
				<th>Max Log Age (days)</th>
				<td>
					<input type="number" name="log_retention_days" value="<?php echo esc_attr( $log_retention_days ); ?>" min="0" step="1" />
					<p class="description">
						Delete log entries older than this many days during the daily cleanup. Set to <b>0</b> to disable (keep forever).
					</p>
				</td>
			</tr>
			</tbody>
		</table>
		<p class="submit">
			<input type="hidden" name="action" value="form-settings-general"/>
			<input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
			<?php wp_nonce_field( 'form-settings-general', 'form-settings-general' ); ?>
		</p>
	</form>
</div>
