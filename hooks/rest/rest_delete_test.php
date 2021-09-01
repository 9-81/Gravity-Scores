<?php

add_filter('gravityscores_rest_delete_test', function ($atts) {

    global $wpdb;
    $data = $atts->get_params();
    $table = $wpdb->prefix . 'gs_tests';
    $eval_table = $wpdb->prefix . 'gs_evaluations';
    $con_table = $wpdb->prefix . 'gs_evaluation_subscale';
    
    // Delete Test
    $wpdb->query($wpdb->prepare("DELETE FROM `$table` WHERE `id` = %d", $data['id']));

    // Delete Evaluations that have zero subscales
    $wpdb->query("DELETE FROM `$eval_table` ev WHERE (( SELECT COUNT(*) FROM `$con_table` con WHERE ev.id = con.id ) != 0)");

    return $atts;
});
