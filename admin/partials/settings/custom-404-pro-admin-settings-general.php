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
						<input type="checkbox" id="c4p_log_email" name="c4p_log_email" <?php echo get_option( 'c4p_log_email' ) == true ? 'checked' : '' ?>  />
						<p class="description">
							If you check this, an email will be sent on every 404 log on the admin's email account.
						</p>
					</td>
				</tr>
			</tbody>
		</table>
		<p class="submit">
		<input type='hidden' name='action' value='settings-general-form' />
		<input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
		</p>
	</form>
</div>
