<?php

if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class LogsClass extends WP_List_Table {

	function __construct(){
        global $status, $page;
        parent::__construct(array(
            'singular' => 'log',
            'plural' => 'logs',
            'ajax' => false
        ));
    }

    function prepare_items() {
    	global $wpdb;
    	$columns = self::get_columns();
    	$hidden = array();
    	$sortable = self::get_sortable_columns();
    	$this->_column_headers = array($columns, $hidden, $sortable);
    	$table_logs = $wpdb->prefix . "custom_404_pro_logs";
    	$sql = "SELECT * FROM " . $table_logs;

    	$order_by = $_GET["orderby"];
    	$order = strtoupper($_GET["order"]);
    	if(!empty($order_by) && !empty($order)) {
    		$sql = self::manage_sorting($order_by, $order, $sql);
    	}

    	$search = $_GET["s"];
    	if(!empty($search)) {
    		$sql = self::manage_search($search, $sql);
    	}

    	$sql_data = $wpdb->get_results($sql);
    	$data = array();
    	for($i = 0; $i < count($sql_data); $i++) {
    		$temp = array();
    		$temp["id"] = $sql_data[$i]->id;
    		$temp["ip"] = $sql_data[$i]->ip;
    		$temp["path"] = $sql_data[$i]->path;
    		$temp["referer"] = $sql_data[$i]->referer;
    		$temp["user_agent"] = $sql_data[$i]->user_agent;
    		$temp["created"] = $sql_data[$i]->created;
    		array_push($data, $temp);
    	}
    	$per_page = 50;
    	$current_page = $this->get_pagenum();
    	$total_items = count($data);
    	$this->set_pagination_args(array(
    		"total_items" => $total_items,
    		"per_page" => $per_page
    	));
    	$data = array_slice($data, (($current_page - 1) * $per_page), $per_page);
    	$this->items = $data;
    }

    function manage_sorting($order_by, $order, $sql) {
    	if($order_by === "created") {
			$sql .= " ORDER BY created";
		} else if($order_by === "u") {
			$sql .= " ORDER BY user_agent";
		} else if($order_by === "i") {
			$sql .= " ORDER BY ip";
		} else if($order_by === "p") {
			$sql .= " ORDER BY path";
		} else if($order_by === "r") {
			$sql .= " ORDER BY referer";
		}
		$sql .= " " . $order;
    	return $sql;
    }

    function manage_search($search, $sql) {
    	$sql .= " WHERE (ip LIKE '%" . $search . "%' OR path LIKE '%" . $search . "%' OR referer LIKE '%" . $search . "%' OR user_agent LIKE '%" . $search . "%' OR created LIKE '%" . $search . "%')";
    	return $sql;
    }

    function get_columns() {
    	$columns = array(
            "cb" => "<input type='checkbox' />",
            "ip" => "IP",
            "path" => "Path",
            "referer" => "Referer",
            "user_agent" => "User Agent",
            "created" => "Created"
        );
        return $columns;
    }

    function column_default($item, $column_name) {
    	switch($column_name) {
    		case "cb": return $item["id"];
    		case "ip": return $item["ip"];
    		case "path": return $item["path"];
    		case "referer": return $item["referer"];
    		case "user_agent": return $item["user_agent"];
    		case "created": return $item["created"];
    	}
    }

    function get_sortable_columns() {
    	$sortable_columns["ip"] = "ip";
    	$sortable_columns["path"] = "path";
    	$sortable_columns["referer"] = "referer";
    	$sortable_columns["user_agent"] = "user_agent";
    	$sortable_columns["created"] = array("created", true);
		return $sortable_columns;
    }

    function column_ip($item) {
        $actions = array(
            'delete' => sprintf('<a href="?page=%s&action=%s&path=%s">Delete</a>',$_REQUEST['page'],'delete',$item['id']),
        );
        return sprintf('%1$s %2$s',
            /*$1%s*/ $item['ip'],
            /*$2%s*/ $this->row_actions($actions)
        );
    }

    function column_cb($item) {
    	return '<input type="checkbox" name="path[]" value="' . $item["id"] . '" />';
    }

    function get_bulk_actions() {
    	$actions = array(
			"delete" => "Delete"
		);
		return $actions;
    }



}
