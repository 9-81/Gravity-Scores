<?php

use \gravityscores\MessageSystem as MessageSystem;

add_action('shutdown', function () {
    $messages = \apply_filters('gravityscores_log_messages', [
        'success' => MessageSystem::get_success_messages(),
        'error' => MessageSystem::get_error_messages(),
        'log' => MessageSystem::get_log_messages()
    ]);


    if (!empty($messages['success'] || !empty($messages['error']) || !empty($messages['log']))) {
        $user_id = get_current_user_id();
        $user_login = ($user_id != 0) ? get_userdata($user_id)->user_login : null;
        $request_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

        ob_start();
        include plugin_dir_path(GS_INDEX) . 'tpl/log.php';
        $log = ob_get_clean();

        $log = \apply_filters('gravityscores_log_entry', $log);
        file_put_contents(plugin_dir_path(GS_INDEX) . 'log/' . date('Ymd_His') . '_log.md', $log);
    }
});
