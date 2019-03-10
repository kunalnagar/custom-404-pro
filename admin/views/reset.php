<div class="wrap">
	<h2>Reset (Important, read carefully)</h2>
	<?php if($_GET["message"] === "updated"): ?>
	<div class="updated">
		<p>Old logs (prior to version 3.0.0) deleted successfully!</p>
	</div>
	<?php endif; ?>
	<br /><form method="post" action="<?php echo get_admin_url() . 'admin-post.php'; ?>">
		<p class="description">
			Version 3.0.0 of the plugin has been re-written from the ground up and fixes a ton of bugs that users have been facing. These include performance and SEO issues related to the logs being created. We have also moved to a new logging model. <br /><br />
			If you've directly installed version 3.0.0, you have nothing to worry about. <br /><br />
			<b>If you've been using the plugin before version 3.0.0, there is no way to export your old logs (for now). However, there is a way to delete them if you want to start from scratch or if you're facing performance issues. If your old logs are important to you, we recommend that you DO NOT press the Reset button below. Version 3.0.1 will have an option to migrate your old logs to the new logging system. Apologies for the inconvenience.</b>
		</p>
		<br /><hr /><br />
		<p>
			<b>Press the Reset button below to clear all old logs (prior to version 3.0.0 of the plugin).</b>
		</p>
		<p class="submit">
			<input type="hidden" name="action" value="form-reset"/>
			<input type="submit" name="submit" id="submit" class="button button-primary" value="Reset">
		</p>
	</form>
</div>