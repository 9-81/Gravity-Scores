<?php

use \gravityscores\JavaScript as JavaScript;
use \gravityscores\Functions as Functions;

add_action('gravityscores_admin_evaluation', function () {
    wp_enqueue_style('gravityscores_admin_all_evaluation', plugin_dir_url(GS_INDEX) . "/css/admin_evaluation.css");
    wp_enqueue_script('gs_admin_evaluation', plugin_dir_url(GS_INDEX) . 'jsdist/admin/admin_evaluation.js', [], false, true);
    JavaScript::add_urls('gs_admin_evaluation');
    JavaScript::add_nonce('gs_admin_evaluation', 'wp_rest');
    add_filter('admin_footer_text', function () {});
    Functions::admin_page_import('evaluation', []);
});
