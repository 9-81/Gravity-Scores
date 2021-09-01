<?php

add_filter('gravityscores_rest_delete_group_result', function ($atts) {

    global $wpdb;
    $data = $atts->get_params();
    $table = $wpdb->prefix . 'gs_group_results';

    if (array_key_exists('id', $data )) {
        $wpdb->query($wpdb->prepare("DELETE FROM `$table` WHERE `id` = %d", $data['id']));
    }
    
    return $atts;
});
