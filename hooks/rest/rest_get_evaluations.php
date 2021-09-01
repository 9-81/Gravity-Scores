<?php

use \gravityscores\Export as Export;

add_filter('gravityscores_rest_get_evaluations', function ($atts) {
    global $wpdb;
    $table = $wpdb->prefix . 'gs_evaluations';
    $results = $wpdb->get_results("SELECT `id` FROM $table");

    $ids = array_map(function ($id_container) {
        return $id_container->id;
    }, $results);

    $options = ['tests' => false, 'visualizations' => false];
    
    return Export::evaluations($ids, $options);
});
