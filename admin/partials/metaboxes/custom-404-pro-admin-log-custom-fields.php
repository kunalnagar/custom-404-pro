<?php

global $post;
$c4p_log_ip          = get_post_meta( $post->ID, 'c4p_log_ip', true );
$c4p_log_404_path    = get_post_meta( $post->ID, 'c4p_log_404_path', true );
$c4p_log_user_agent  = get_post_meta( $post->ID, 'c4p_log_user_agent', true );
$c4p_log_redirect_to = get_post_meta( $post->ID, 'c4p_log_redirect_to', true );
?>

<table class="form-table">
	<tbody>
	<tr>
		<td>IP</td>
		<td>
			<input type="text" name="c4p_log_ip" value="<?php echo $c4p_log_ip; ?>" placeholder="User IP Address"
			       class="widefat" disabled/>
		</td>
	</tr>
	<tr>
		<td>404 Path</td>
		<td>
			<input type="text" name="c4p_log_404_path" value="<?php echo $c4p_log_404_path; ?>"
			       placeholder="Path that led to 404" class="widefat" disabled/>
		</td>
	</tr>
	<tr>
		<td>User Agent</td>
		<td>
			<textarea name="c4p_log_user_agent" rows="3" class="widefat"
			          disabled><?php echo $c4p_log_user_agent; ?></textarea>
		</td>
	</tr>
	<tr>
		<td>Redirect</td>
		<td>
			<input type="text" name="c4p_log_redirect_to" value="<?php echo $c4p_log_redirect_to; ?>"
			       placeholder="Where does this 404 redirect to?" class="widefat"/>
		</td>
	</tr>
	</tbody>
</table>
