<?php

use \gravityscores\Export as Export;

add_filter('gravityscores_rest_get_groups', function ($atts) {
    global $wpdb;
    $table = $wpdb->prefix . 'gs_groups';
    return $wpdb->get_results("SELECT * FROM $table");
});
