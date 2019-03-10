<?php

global $wpdb;

$table_logs = $wpdb->prefix . "custom_404_pro_logs";

$action = $_REQUEST["action"];

if($action === "delete") {
	if(is_array($_REQUEST["path"])) {
		$sql_delete = "DELETE FROM " . $table_logs . " WHERE id in (" . implode(",", $_REQUEST["path"]) . ")";
	} else {
		$sql_delete = "DELETE FROM " . $table_logs . " WHERE id=" . $_REQUEST["path"] . "";
	}
	$wpdb->query($sql_delete);
}

require_once(__DIR__ . '../../LogsClass.php');

$logs_table = new LogsClass();
$logs_table->prepare_items();

?>

<div class="wrap">
	<h2>Logs</h2>
	<form id="form_logs" method="get">
        <!-- For plugins, we also need to ensure that the form posts back to our current page -->
        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
        <!-- Now we can render the completed list table -->
        <p class="search-box">
			<label class="screen-reader-text" for="search_id-search-input">Search</label>
			<input id="search_id-search-input" type="text" name="s" value="<?php echo $_GET["s"]; ?>" autocomplete="off" />
			<input id="search-submit" class="button" type="submit" name="" value="Search" />
		</p><br /><br />
        <?php $logs_table->display(); ?>
    </form>
</div>