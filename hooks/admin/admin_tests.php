<?php

use \gravityscores\JavaScript as JavaScript;
use \gravityscores\Functions as Functions;

add_action('gravityscores_admin_tests', function () {
    wp_enqueue_style('gravityscores_admin_all_tests', plugin_dir_url(GS_INDEX) . "/css/admin_tests.css");
    wp_enqueue_script('gs_admin_tests', plugin_dir_url(GS_INDEX) . 'jsdist/admin/admin_tests.js', [], false, true);
    JavaScript::add_urls('gs_admin_tests');
    JavaScript::add_nonce('gs_admin_tests', 'wp_rest');
    add_filter('admin_footer_text', function () {});

    Functions::admin_page_import('tests', []);
});
