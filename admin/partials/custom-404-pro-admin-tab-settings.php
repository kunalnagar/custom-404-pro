<div class="wrap">
	<?php if ( $_GET['message'] == 'c4p-settings-updated' ): ?>
	<br>
	<div class="updated">
		<p>Settings Saved!</p>
	</div>
	<?php endif; ?>
    <form method="post" action="<?php echo get_admin_url() . 'admin-post.php'; ?>">
        <table class="form-table">
            <tbody>
                <tr>
                    <th>Clear Logs</th>
                    <td>
                        <input type="checkbox" id="c4p_clear_logs" name="c4p_clear_logs" />
                    </td>
                </tr>
            </tbody>
        </table>
        <p class="submit">
        <input type='hidden' name='action' value='c4p-settings-form' />
        <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
        </p>
    </form>
</div>
