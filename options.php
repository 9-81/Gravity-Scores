<?php

return [
    'capabilities' => [
        'gravityscores_list_evaluations' => 'List Evaluations',
        'gravityscores_add_evaluation' => 'Add Evaluation',
        'gravityscores_list_tests' => 'List Tests',
        'gravityscores_add_test' => 'Add Test',
        'gravityscores_export' => 'Export Tests or Evaluations',
        'gravityscores_import' => 'Import Tests or Evaluations',
        'gravityscores_uninstall' => 'Uninstall Gravity Scores',
        'gravityscores_view_log' => 'View Logs'
    ],
    'requirements' => [
        'php_version' => '7.2.0',
        'wordpress_version' => '5.5',
        'gravityscores' => 'gravityforms/gravityforms.php'
    ],
    'repository' => [
        'url' => 'https://gitlab.rlp.net/jdillenberger/gravityscores',
        'issue_url' => 'https://gitlab.rlp.net/jdillenberger/gravityscores/-/issues/new'
    ],
    'debug_mode' => true

];
