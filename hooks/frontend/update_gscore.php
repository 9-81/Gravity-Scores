<?php

use \gravityscores\JavaScript as JavaScript;

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_script('gs_gscore', plugin_dir_url(GS_INDEX) . 'js/frontend/update_gscore.js', [], false, true);
    JavaScript::add_urls('gs_gscore');
});
