<?php

use \gravityscores\Import as Import;

add_filter('gravityscores_rest_append_subscale', function ($atts) {

    global $wpdb;
    $subscale = $atts->get_params();
    $table = $wpdb->prefix . 'gs_subscales';
    $query = "INSERT INTO $table ( `test_id`, `name`, `description` ) VALUES ( %d, %s, %s )";
    $wpdb->query($wpdb->prepare($query, $subscale['test_id'], $subscale['name'], $subscale['description']));
    $subscale['id'] = $wpdb->insert_id;
    $subscale['evaluables'] = [];
    $subscale['group_results'] = [];
    return $subscale;
  
});
