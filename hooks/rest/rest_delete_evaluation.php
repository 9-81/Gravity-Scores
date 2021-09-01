<?php

add_filter('gravityscores_rest_delete_evaluation', function ($atts) {

    global $wpdb;
    $data = $atts->get_params();
    $table = $wpdb->prefix . 'gs_evaluations';
    $con_table = $wpdb->prefix . 'gs_evaluation_subscale';

    $wpdb->query($wpdb->prepare("DELETE FROM `$table` WHERE `id` = %d", $data['id']));
    
    return $atts;
});
