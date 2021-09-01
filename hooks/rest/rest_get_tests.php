<?php

use \gravityscores\Export as Export;

add_filter('gravityscores_rest_get_tests', function ($atts) {
    global $wpdb;
    $table = $wpdb->prefix . 'gs_tests';
    $result = $wpdb->get_results("SELECT `id` FROM $table");
    
    $ids = array_map(function ($id_container) {
        return $id_container->id;
    }, $result);

    return Export::tests($ids, ['subscales' => false]);
});
