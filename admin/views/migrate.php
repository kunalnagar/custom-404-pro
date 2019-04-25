<?php

$old_logs_count = Helpers::get_old_logs_count();

?>

<div class="wrap">
	<h2>Migrate (Important, read carefully)</h2>
	<?php if ( array_key_exists( 'message', $_GET ) ) : ?>
		<?php if ( $_GET['message'] === 'updated' ) : ?>
		<div class="updated">
			<p>Old logs (prior to version 3.0.0) deleted successfully!</p>
		</div>
		<?php endif; ?>
	<?php endif; ?>
	<br /><form method="post" action="<?php echo get_admin_url() . 'admin-post.php'; ?>">
		<p class="description">
			Version 3.0.0 of the plugin has been re-written from the ground up and fixes a ton of bugs that users have been facing. These include performance and SEO issues related to the logs being created. We have also moved to a new logging model. <br /><br />
			If you've directly installed version 3.0.0, you have nothing to worry about and can safely ignore this page. <br /><br />
			<b>If you've been using the plugin before version 3.0.0, use the migrate button to transfer your older logs to the new logging system. <br /><br />Please note that due to technical limitations and to be safe, the migration will be done in batches of <u>500</u> logs. So you might have to use the button below multiple times. In any case, the old logs count will be displayed so you have an idea when the process is complete.</b>
		</p>
		<br /><hr /><br />
		<p>
			<b>Press the Migrate button below to migrate old logs (prior to version 3.0.0 of the plugin).</b>
		</p>
		<p>
			Old Logs Count: <?php echo $old_logs_count; ?>
		</p>
		<p class="submit">
			<input type="hidden" name="action" value="form-migrate"/>
			<input type="submit" name="submit" id="submit" class="button button-primary" value="Migrate">
		</p>
	</form>
</div>
