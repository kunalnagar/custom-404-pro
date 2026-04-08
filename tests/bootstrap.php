<?php

// Stub $wpdb so class constructors can reference it without WordPress loaded
global $wpdb;
$wpdb         = new stdClass();
$wpdb->prefix = 'wp_';

require_once dirname( __DIR__ ) . '/admin/Helpers.php';
require_once dirname( __DIR__ ) . '/admin/AdminClass.php';
