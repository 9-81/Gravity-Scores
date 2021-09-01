<?php

use \gravityscores\Functions as Functions;

add_action('admin_menu', function () {
    if (!Functions::is_plugin_active('gravityforms/gravityforms.php')) {
        return;
    }

    // SCORES
    add_menu_page(
        __('Gravity Scores', 'gravityscores'),
        __('Scores', 'gravityscores'),
        'gravityscores_list_evaluations',
        'gravityscores',
        function () {
        },
        'dashicons-chart-bar',
        17
    );

    // All Evaluations
    add_submenu_page(
        'gravityscores',
        __('All Evaluations', 'gravityscores'),
        __('All Evaluations', 'gravityscores'),
        'gravityscores_list_evaluations',
        'gravityscores',
        function () {
            do_action('gravityscores_admin_evaluations');
        },
        11
    );

    // Add Evaluation
    add_submenu_page(
        'gravityscores',
        __('Add Evaluation', 'gravityscores'),
        __('Add Evaluation', 'gravityscores'),
        'gravityscores_add_evaluation',
        'gravityscores_evaluation',
        function () {
            do_action('gravityscores_admin_evaluation');
        },
        10
    );

    // All Tests
    add_submenu_page(
        'gravityscores',
        __('All Tests', 'gravityscores'),
        __('All Tests', 'gravityscores'),
        'gravityscores_list_tests',
        'gravityscores_tests',
        function () {
            do_action('gravityscores_admin_tests');
        },
        11
    );

    // Add Test
    add_submenu_page(
        'gravityscores',
        __('Add Test', 'gravityscores'),
        __('Add Test', 'gravityscores'),
        'gravityscores_add_test',
        'gravityscores_test',
        function () {
            do_action('gravityscores_admin_test');
        },
        10
    );

    // Export/Import
    add_submenu_page(
        'gravityscores',
        __('Import/Export', 'gravityscores'),
        __('Import/Export', 'gravityscores'),
        current_user_can('gravityscores_export') ? 'gravityscores_export' : 'gravityscores_import',
        'gravityscores_import_export',
        function () {
            do_action('gravityscores_admin_export');
            do_action('gravityscores_admin_import');
        },
        10
    );

    // View Log
    add_submenu_page(
        'gravityscores',
        __('Gravity Scores Log', 'gravityscores'),
        __('View Log', 'gravityscores'),
        'gravityscores_view_log',
        'gravityscores_log',
        function () {
            do_action('gravityscores_admin_log');
        },
        10
    );
});
