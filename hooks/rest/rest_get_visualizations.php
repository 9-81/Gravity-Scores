<?php

use \gravityscores\Export as Export;

add_filter('gravityscores_rest_get_visualizations', function ($atts) {
    global $wpdb;
    $table = $wpdb->prefix . 'gs_visualizations';
    $result = $wpdb->get_results("SELECT `id` FROM $table");

    $ids = array_map(function ($id_container) {
        return $id_container->id;
    }, $result);
    
    return  Export::visualizations($ids, []);
});
