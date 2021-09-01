<?php

add_filter('gravityscores_rest_put_group_result', function ($atts) {

    global $wpdb;
    $data = $atts->get_params();
    $table = $wpdb->prefix . 'gs_group_results';
    $group_table = $wpdb->prefix . 'gs_groups';
    

    $update_query = "UPDATE $table SET `subscale_id` = %d, `group_id` = %d, `data` = %s WHERE `id` = %d";
    $insert_query = "INSERT INTO $table ( `subscale_id`, `group_id`, `data` ) VALUES ( %d, %d, %s )";

    if (!array_key_exists('group_id', $data) && array_key_exists('group', $data) ) {
        $data['group_id'] = $wpdb->get_var($wpdb->prepare("SELECT id FROM $group_table WHERE `name` = %s", $data['group'] ));
    }



    /*if (!array_key_exists('group_id', $data)) {
        throw new Exception('A group or group_id needs to be provided.');
    }*/
    
    if ($data['__inserted__']) {
        $wpdb->query($wpdb->prepare($insert_query, $data['subscale_id'], $data['group_id'], json_encode($data['data'] ?? (object) [])));
        $group_result_id = $wpdb->insert_id;
    } else if ($data['__updated__']  && array_key_exists('id', $data)) {
        $wpdb->query($wpdb->prepare($update_query, $data['subscale_id'], $data['group_id'], json_encode($data['data'] ?? (object) []), $data['id']));
        $group_result_id = $data['id'];
    }

    return $group_result_id;
});
