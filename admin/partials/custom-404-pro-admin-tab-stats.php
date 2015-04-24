<?php
$c4p_404_data = maybe_unserialize( get_option( 'c4p_404_data' ) );
?>
<?php if ( empty( $c4p_404_data ) ): ?>
<div class="wrap">
    <div class="card c4p-full-card">
        <h3>Congratulations!</h3>
        <p>
        You have no 404s. Good going, mate!
        </p>
    </div>
</div>
<?php else: ?>
<div class="wrap">
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
    <table id="c4p_stats_table" class="wp-list-table widefat fixed">
        <thead>
            <tr>
                <th class="manage-column no">#</th>
                <th class="manage-column ip">IP</th>
                <th class="manage-column path">Path</th>
                <!-- <th class="manage-column referer">Referer</th> -->
                <th class="manage-column time">Time</th>
                <th class="manage-column user-agent">User Agent</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ( $c4p_404_data as $key => $data ): ?>
            <tr>
                <td><?php echo $key + 1; ?></td>
                <td><?php echo $data['ip']; ?></td>
                <td><?php echo $data['path']; ?></td>
                <!-- <td><?php echo empty( $data['referer'] ) ? 'N/A' : $data['referer']; ?></td> -->
                <td><?php echo date( 'M d, Y @ h:i:s A', $data['time'] ); ?></td>
                <td>
                    <span class="user-agent"><?php print_r( $data['user_agent_min'] ); ?></span> <i>(<?php print_r( $data['user_agent'] ); ?>)</i>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
