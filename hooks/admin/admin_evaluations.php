<?php

use \gravityscores\JavaScript as JavaScript;
use \gravityscores\Functions as Functions;

add_action('gravityscores_admin_evaluations', function () {
    wp_enqueue_style('gravityscores_admin_all_evaluations', plugin_dir_url(GS_INDEX) . "/css/admin_evaluations.css");
    wp_enqueue_script('gs_admin_evaluations', plugin_dir_url(GS_INDEX) . 'jsdist/admin/admin_evaluations.js', [], false, true);
    JavaScript::add_urls('gs_admin_evaluations');
    JavaScript::add_nonce('gs_admin_evaluations', 'wp_rest');
    add_filter('admin_footer_text', function () {});

    Functions::admin_page_import('evaluations', []);
});
