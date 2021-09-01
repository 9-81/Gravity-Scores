<?php
defined('ABSPATH') or die();

if (!isset($options) || empty($options)) {
    $options = include  __DIR__  . DIRECTORY_SEPARATOR . 'options.php';
}

// DELETE LOGFILES
foreach (glob(plugin_dir_path(GS_INDEX) . 'log/*.md') as $log_file) {
    unlink($log_file);
}

// USER CAPABILITIES TEARDOWN
foreach ($GLOBALS['wp_roles']->role_objects as $role) {
    foreach (array_keys($options['capabilities']) as $del_capability) {
        if (!$role->has_cap($del_capability)) {
            continue;
        }

        $role->remove_cap($del_capability);
    }
}


// DATABASE TEARDOWN
$drop_table_queries = array_map(function($table){
    $table_name = $GLOBALS['wpdb']->prefix . 'gs_' . $table;
    return "DROP TABLE IF EXISTS $table_name;";
},[ 'tests', 'evaluations', 'evaluation_subscale', 'visualizations', 'subscales', 'groups', 'group_results', 'evaluables', 'binary_answers']);

$GLOBALS['wpdb']->query('SET FOREIGN_KEY_CHECKS = 0;');
foreach ($drop_table_queries as $drop_table_query) {
    $GLOBALS['wpdb']->query($drop_table_query);
}
$GLOBALS['wpdb']->query('SET FOREIGN_KEY_CHECKS = 1;');
