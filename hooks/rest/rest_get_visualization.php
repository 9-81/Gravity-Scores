<?php

use \gravityscores\Export as Export;

add_filter('gravityscores_rest_get_visualization', function ($atts) {
    $options = ['clean' => true, 'preserve_ids' => true];
    return Export::visualizations([$atts['id']], $options);
});
