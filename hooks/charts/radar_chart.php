<?php
use \gravityscores\JavaScript as JavaScript;

$chart_name = 'radar_chart';
$min_subscales = 3;
$max_subscales = 12;

add_filter('gs_register_chart', function ($list) use ($chart_name, $min_subscales, $max_subscales) {
    array_push($list, [
        'name' => $chart_name,
        'min_subscales' => $min_subscales,
        'max_subscales' => $max_subscales
    ]);
    return $list;
});

add_filter('gravityscores_shortcode', function ($atts) use ($chart_name) {
    if ($atts['visualization_name'] == $chart_name) {
        wp_enqueue_style('gs_' . $chart_name . '_css', plugin_dir_url(GS_INDEX) . 'css/' . $chart_name . '.css', []);
        wp_enqueue_script('gs_' . $chart_name, plugin_dir_url(GS_INDEX) . 'jsdist/visualizations/' . $chart_name . '.js', []);
    
        JavaScript::add_urls('gs_' .  $chart_name);
        JavaScript::add_nonce('gs_' . $chart_name, 'wp_rest');

        extract($atts);

        include(plugin_dir_path(GS_INDEX) . "tpl/shortcode.php");
    }

    return $atts;
});
