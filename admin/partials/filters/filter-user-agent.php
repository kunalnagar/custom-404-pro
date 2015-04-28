<select name="c4p_user_agent">
	<option value="">All User Agents</option>
	<?php foreach($fields as $field): ?>
	<option value="<?php echo $field['meta_value']; ?>">
		<?php echo $this->get_user_agent($field['meta_value']); ?>
	</option>
	<?php endforeach; ?>
</select>