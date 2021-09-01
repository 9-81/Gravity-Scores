<?php

use \gravityscores\MessageSystem as MessageSystem;

add_action('admin_notices', function () {
    foreach (MessageSystem::get_error_messages() as $message) {
        $admin_notice_class = "notice notice-error";
            
        include plugin_dir_path(GS_INDEX) . 'tpl/admin_notice.php';
    }

    foreach (MessageSystem::get_success_messages() as $message) {
        $admin_notice_class = "notice notice-success";
            
        include plugin_dir_path(GS_INDEX) . 'tpl/admin_notice.php';
    }
});
