<div class="wrap">
	<?php if ( $_GET['message'] == 'c4p-settings-updated' ): ?>
	<br>
	<div class="updated">
		<p>Settings Saved!</p>
	</div>
	<?php endif; ?>
	<table class="form-table">
		<tbody>
			<tr>
				<th>Choose Format</th>
				<td>
					<select id="c4p_logs_download_options">
						<option value="">None</option>
						<option value="csv">CSV</option>
						<!-- <option value="pdf">PDF</option> -->
						<option value="json">JSON</option>
						<option value="xml">XML</option>
						<option value="txt">TXT</option>
						<option value="sql">SQL</option>
						<option value="doc">MS-WORD (.docx)</option>
						<option value="excel">MS-EXCEL (.xlsx)</option>
						<option value="powerpoint">MS-POWERPOINT (.pptx)</option>
					</select>
				</td>
			</tr>
		</tbody>
	</table>
	<p class="submit">
	<input type="hidden" id="c4p_logs_download_format" value="" />
	<input type="button" id="c4p_logs_download" disabled class="button button-primary" value="Download Logs" />
	</p>
	<hr>
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
