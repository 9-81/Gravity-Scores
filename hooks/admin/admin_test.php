<?php

use \gravityscores\JavaScript as JavaScript;
use \gravityscores\Functions as Functions;

add_action('gravityscores_admin_test', function () {
    wp_enqueue_style('gravityscores_admin_add_test', plugin_dir_url(GS_INDEX) . "/css/admin_test.css");
    wp_enqueue_script('gs_admin_test', plugin_dir_url(GS_INDEX) . 'jsdist/admin/admin_test.js', [], false, true);
    JavaScript::add_urls('gs_admin_test');

    add_filter('admin_footer_text', function () {});

    Functions::admin_page_import('test', []);
});
