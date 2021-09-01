<?php

use \gravityscores\Functions as Functions;

add_action('gravityscores_admin_log', function () use ($options) {
    wp_enqueue_style('gs_admin', plugin_dir_url(GS_INDEX) . "/css/admin.css");
    wp_enqueue_style('gs_admin_log', plugin_dir_url(GS_INDEX) . "/css/admin_log.css");
    wp_enqueue_script('axios', plugin_dir_url(GS_INDEX) . 'node_modules/axios/dist/axios.min.js', [], false, true);
    wp_enqueue_script('marked', plugin_dir_url(GS_INDEX) . 'node_modules/marked/marked.min.js', [], false, true);
    wp_enqueue_script('gs_admin_log', plugin_dir_url(GS_INDEX) . 'jsdist/admin/admin_log.js', [], false, true);

    $logs = [];
    $log_file_names = array_map(function ($log_file) {
        return str_replace(plugin_dir_path(GS_INDEX) . 'log/', '', $log_file);
    }, glob(plugin_dir_path(GS_INDEX) . 'log/*.md'));
    
    rsort($log_file_names);

    foreach ($log_file_names as $log_file) {
        $year = substr($log_file, 0, 4);
        $month = substr($log_file, 4, 2);
        $day = substr($log_file, 6, 2);

        $hour = substr($log_file, 9, 2);
        $minute = substr($log_file, 11, 2);
        $second = substr($log_file, 13, 2);

        array_push($logs, [
            "date" => "$day.$month.$year",
            "time" => "$hour:$minute",
            "url" => plugin_dir_url(GS_INDEX) . "log/$log_file"
        ]);
    }

    Functions::admin_page_import('log', ['logs' => $logs, 'options' => $options]);
});
