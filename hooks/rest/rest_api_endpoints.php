<?php
defined('ABSPATH') or die();

$permission_callback_nonce = function ($request) {
    $headers = getallheaders();
    return isset($headers['X-WP-Nonce']) && !empty($headers['X-WP-Nonce']);
};

add_action('rest_api_init', function () use ($permission_callback_nonce) {

    // Delete group result
    register_rest_route('gravityscores/v1', '/group_result/(?P<id>\d+)', array(
        'methods' => 'DELETE',
        'permission_callback' => $permission_callback_nonce,
        'callback' => function ($atts) {
            return \apply_filters('gravityscores_rest_delete_group_result', $atts);
        },
    ));

    // Put group result
    register_rest_route('gravityscores/v1', '/group_result/', array(
        'methods' => 'PUT',
        'permission_callback' => $permission_callback_nonce,
        'callback' => function ($atts) {
            return \apply_filters('gravityscores_rest_put_group_result', $atts);
        },
    ));


    // Append Subscale
    register_rest_route('gravityscores/v1', '/subscale/', array(
        'methods' => 'POST',
        'permission_callback' => $permission_callback_nonce,
        'callback' => function ($atts) {
            return \apply_filters('gravityscores_rest_append_subscale', $atts);
        },
    ));

    // Single Evaluation
    register_rest_route('gravityscores/v1', '/evaluation/(?P<id>\d+)', array(
        'methods' => 'GET',
        'permission_callback' => $permission_callback_nonce,
        'callback' => function ($atts) {
            return \apply_filters('gravityscores_rest_get_evaluation', $atts);
        },
    ));

    // Delete Evaluation
    register_rest_route('gravityscores/v1', '/evaluation/(?P<id>\d+)', array(
        'methods' => 'DELETE',
        'permission_callback' => $permission_callback_nonce,
        'callback' => function ($atts) {
            return \apply_filters('gravityscores_rest_delete_evaluation', $atts);
        },
    ));

    // Single Visualization
    register_rest_route('gravityscores/v1', '/visualization/(?P<id>\d+)', array(
        'methods' => 'GET',
        'permission_callback' => '__return_true',
        'callback' => function ($atts) {
            return \apply_filters('gravityscores_rest_get_visualization', $atts);
        },
    ));

    // Single Test
    register_rest_route('gravityscores/v1', '/test/(?P<id>\d+)', array(
        'methods' => 'GET',
        'permission_callback' => $permission_callback_nonce,
        'callback' => function ($atts) {
            return \apply_filters('gravityscores_rest_get_test', $atts);
        },
    ));

    // Delete Test
    register_rest_route('gravityscores/v1', '/test/(?P<id>\d+)', array(
        'methods' => 'DELETE',
        'permission_callback' => $permission_callback_nonce,
        'callback' => function ($atts) {
            return \apply_filters('gravityscores_rest_delete_test', $atts);
        },
    ));

    // Extendet fields of a single form
    register_rest_route('gravityscores/v1', '/fields/(?P<id>\d+)', array(
        'methods' => 'GET',
        'permission_callback' => '__return_true',
        'callback' => function ($atts) {
            return \apply_filters('gravityscores_rest_get_form_fields', $atts);
        },
    ));

    // Single Form
    register_rest_route('gravityscores/v1', '/form/(?P<id>\d+)', array(
        'methods' => 'GET',
        'permission_callback' => '__return_true',
        'callback' => function ($atts) {
            return \apply_filters('gravityscores_rest_get_form', $atts);
        },
    ));

    // All Evaluations
    register_rest_route('gravityscores/v1', '/evaluations', array(
        'methods' => 'GET',
        'permission_callback' => '__return_true',
        'callback' => function ($atts) {
            return \apply_filters('gravityscores_rest_get_evaluations', $atts);
        },
    ));

    // All Visualizations
    register_rest_route('gravityscores/v1', '/visualizations', array(
        'methods' => 'GET',
        'permission_callback' => '__return_true',
        'callback' => function ($atts) {
            return \apply_filters('gravityscores_rest_get_visualizations', $atts);
        },
    ));

    // All Tests
    register_rest_route('gravityscores/v1', '/tests', array(
        'methods' => 'GET',
        'permission_callback' => '__return_true',
        'callback' => function ($atts) {
            return \apply_filters('gravityscores_rest_get_tests', $atts);
        },
    ));

    // All Forms
    register_rest_route('gravityscores/v1', '/forms', array(
        'methods' => 'GET',
        'permission_callback' => '__return_true',
        'callback' => function ($atts) {
            return \apply_filters('gravityscores_rest_get_forms', $atts);
        },
    ));


    // All Forms
    register_rest_route('gravityscores/v1', '/groups', array(
        'methods' => 'GET',
        'permission_callback' => '__return_true',
        'callback' => function ($atts) {
            return \apply_filters('gravityscores_rest_get_groups', $atts);
        },
    ));


    // New Import
    register_rest_route('gravityscores/v1', '/import', array(
        'methods' => 'POST',
        'permission_callback' => '__return_true',
        'callback' => function ($atts) {
            return \apply_filters('gravityscores_rest_import', $atts);
        },
    ));
    
    // Update Import
    register_rest_route('gravityscores/v1', '/import', array(
        'methods' => 'PUT',
        'permission_callback' => '__return_true',
        'callback' => function ($atts) {
            return \apply_filters('gravityscores_rest_import', $atts);
        },
    ));
});
