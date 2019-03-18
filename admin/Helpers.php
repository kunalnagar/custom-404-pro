<?php

class Helpers {

    private static $instance;

    public static function singleton() {
        static $inst = null;
        if($inst === null) {
            $inst = new Helpers();
        }
        return $inst;
    }

    public function __construct() {
        global $wpdb;
        $this->table_options = $wpdb->prefix . "custom_404_pro_options";
        $this->table_logs = $wpdb->prefix . "custom_404_pro_logs";
        $this->options_defaults = array();
        $options_defaults_temp = array(
            "mode" => "",
            "mode_page" => "",
            "mode_url" => "",
            "send_email" => "",
            "logging_enabled" => "",
            "redirect_error_code" => 302,
            "log_ip" => true
        );
        foreach($options_defaults_temp as $key => $value) {
            $obj = new stdClass();
            $obj->name = $key;
            $obj->value = $value;
            array_push($this->options_defaults, $obj);
        }
    }

    // public function plugin_data() {
    //     $plugin_main_file = dirname(__FILE__) . '/custom-404-pro/custom-404-pro.php';
    //     $plugin_data = get_plugin_data($plugin_main_file);
    //     return $plugin_data;
    // }

    public function print_pretty($result) {
        echo "<pre>";
        var_dump($result);
        echo "</pre>";
    }

    public function initialize_table_options() {
        global $wpdb;
        $count = count($this->options_defaults);
        $sql = "INSERT INTO " . $this->table_options . " (name, value) VALUES ";
        foreach($this->options_defaults as $key => $option) {
            if($key !== ($count - 1)) {
                $sql .= "('" . $option->name . "', '" . $option->value . "'),";
            } else {
                $sql .= "('" . $option->name . "', '" . $option->value . "')";
            }
        }
        $wpdb->query($sql);
    }

    public function is_option($option_name) {
        global $wpdb;
        $query = "SELECT * FROM " . $this->table_options . " WHERE name='" . $option_name . "'";
        $result = $wpdb->get_results($query);
        if(empty($result)) {
            return false;
        } else {
            return $result[0];
        }
    }

    public function get_option($option_name) {
        global $wpdb;
        $query = "SELECT value FROM " . $this->table_options . " WHERE name='" . $option_name . "'";
        $result = $wpdb->get_var($query);
        return $result;
    }

    public function insert_option($option_name, $option_value) {
        global $wpdb;
        $result = $wpdb->insert(
            $this->table_options,
            array(
                "name" => $option_name,
                "value" => $option_value
            )
        );
        return $result;
    }

    public function update_option($option_name, $option_value) {
        global $wpdb;
        $result = $wpdb->update(
            $this->table_options,
            array("value" => $option_value),
            array("name" => $option_name)
        );
        return $result;
    }

    public function upsert_option($option_name, $option_value) {
        global $wpdb;
        if(self::is_option($option_name)) {
            $result = self::update_option($option_name, $option_value);
        } else {
            $result = self::insert_option($option_name, $option_value);
        }
        return $result;
    }

}

?>