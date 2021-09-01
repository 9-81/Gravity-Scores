<?php

use \gravityscores\MessageSystem as MessageSystem;
use \gravityscores\Export as Export;
use \gravityscores\JavaScript as JavaScript;

add_shortcode('gravityscores', function ($atts) {
    if (!array_key_exists('id', $atts)) {
        return __('You need to provide an evaluation `id` in your shortcode.', 'gravityscores');
    }

    global $wpdb;

    $evaluations_table =  $wpdb->prefix . '_gs_evaluations';
    $visualizations_table =  $wpdb->prefix . '_gs_visualizations';
    
    $export = Export::evaluations([$atts['id']], ['subscales' => false]);
    
    if (!isset($export['visualizations']) ||  empty($export['visualizations'])) {
        MessageSystem::error('A short code requested either a **visualization** or an **evaluation** that **did not exist**. The given evaluation-id was ' . $atts['id'] ?? '<em>unknown</em>');
        return '<p class="gs_error"> Visualization is not available. See Gravity Scores logs for more information.</p>';
    }

    $visualization_name = $export['visualizations'][0]['name'];

    $form_ids = array_map(function ($test) {
        return $test['form_id'];
    }, $export['tests']);

    if (in_array('buttons', $atts) || in_array('visualization', $atts)) {
        $show_visualization = $atts['visualization'] ?? false;
        $show_buttons = $atts['buttons'] ?? false;
    } else {
        $show_visualization = true;
        $show_buttons = true;
    }

    ob_start();

    apply_filters('gravityscores_shortcode', [
        'shortcode_attributes' => $atts,
        'visualization_name' => $visualization_name,
        'show_buttons' => $show_buttons,
        'show_visualization' => $show_visualization,
        'form_ids' => $form_ids
    ]);

    return ob_get_clean();
});
