<?php
$c4p_404_data = maybe_unserialize( get_option( 'c4p_404_data' ) );
?>
<div class="wrap">
	<br><p class="description">
		This section contains the Stats for various 404s on your website.
	</p><br>
	<table id="c4p_stats_table" class="wp-list-table widefat fixed">
		<thead>
			<tr>
				<th class="manage-column no">#</th>
				<th class="manage-column ip">IP</th>
				<th class="manage-column path">Path</th>
				<th class="manage-column referer">Referer</th>
				<th class="manage-column time">Time</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $c4p_404_data as $key => $data ): ?>
			<tr>
				<td><?php echo $key + 1; ?></td>
				<td><?php echo $data['ip']; ?></td>
				<td><?php echo $data['path']; ?></td>
				<td><?php echo empty( $data['referer'] ) ? 'N/A' : $data['referer']; ?></td>
				<td><?php echo date( 'M d, Y @ h:i:s A', $data['time'] ); ?></td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>
