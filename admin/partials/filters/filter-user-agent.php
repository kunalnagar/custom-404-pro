<select name="ua_browser_names">
	<option value="">Browsers</option>
	<?php foreach ( $agent_name_fields as $field ): ?>
		<option
			value="<?php echo $field['meta_value']; ?>" <?php if ( $_GET['ua_browser_names'] === $field['meta_value'] ) {
			echo 'selected';
		} ?>>
			<?php echo $field['meta_value']; ?>
		</option>
	<?php endforeach; ?>
</select>
<select name="ua_browser_versions">
	<option value="">Browser Versions</option>
	<?php foreach ( $agent_version_fields as $field ): ?>
		<option
			value="<?php echo $field['meta_value']; ?>" <?php if ( $_GET['ua_browser_versions'] === $field['meta_value'] ) {
			echo 'selected';
		} ?>>
			<?php echo $field['meta_value']; ?>
		</option>
	<?php endforeach; ?>
</select>
<select name="ua_os_types">
	<option value="">OS Types</option>
	<?php foreach ( $os_type_fields as $field ): ?>
		<option value="<?php echo $field['meta_value']; ?>" <?php if ( $_GET['ua_os_types'] === $field['meta_value'] ) {
			echo 'selected';
		} ?>>
			<?php echo $field['meta_value']; ?>
		</option>
	<?php endforeach; ?>
</select>
<?php submit_button( __( 'Reset' ), 'button', 'filter_reset', false, array( 'id' => 'post-query-submit' ) ); ?>