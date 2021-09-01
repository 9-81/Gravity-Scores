<?php
/**
 * Plugin Name:  Gravity Scores
 * Plugin URI:  https //gitlab.uni-koblenz.de/jdillenberger/gravitsycores
 * Description:  Plugin to calculate and display results of a GravityForms-Test
 * Version:  1.0
 * Author:  Jan Dillenberger
 * License:  GPLv2
*/

defined('ABSPATH') or die();
define('GS_INDEX', __FILE__);

// Loads data thats used globally in the plugin
$options = include plugin_dir_path(GS_INDEX) . 'options.php';

// Show warnings in debug mode
if ($options['debug_mode']) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
}

/**
 * INCLUDES
 * Each line adds a set of library functions that can be used
 * elsewhere in the application. Deactivating one of those
 * parts could break another component. Additionally they depend
 * on each other, therfore include order matters.
 */
# LOGGING
require_once plugin_dir_path(GS_INDEX) . 'inc/messages.php';

# HEPLERS
require_once plugin_dir_path(GS_INDEX) . 'inc/functions.php';
require_once plugin_dir_path(GS_INDEX) . 'inc/database.php';
require_once plugin_dir_path(GS_INDEX) . 'inc/javascript.php';

# DATA MODEL
require_once plugin_dir_path(GS_INDEX) . 'inc/import.php';
require_once plugin_dir_path(GS_INDEX) . 'inc/export.php';
require_once plugin_dir_path(GS_INDEX) . 'inc/scores.php';

/**
 * HOOKS
 * Each include here adds one specific functionaliy to the plugin.
 * Comment the corresponding line to deactivate the function.
 */

# GENERAL
include plugin_dir_path(GS_INDEX) . 'hooks/plugin/activation.php';
include plugin_dir_path(GS_INDEX) . 'hooks/plugin/deactivation.php';
include plugin_dir_path(GS_INDEX) . 'hooks/plugin/dependencies.php';
include plugin_dir_path(GS_INDEX) . 'hooks/plugin/log.php';


# ADMIN FUNCTIONS
include plugin_dir_path(GS_INDEX) . 'hooks/admin/admin_evaluation.php';
include plugin_dir_path(GS_INDEX) . 'hooks/admin/admin_evaluations.php';
include plugin_dir_path(GS_INDEX) . 'hooks/admin/admin_import_export.php';
include plugin_dir_path(GS_INDEX) . 'hooks/admin/admin_log.php';
include plugin_dir_path(GS_INDEX) . 'hooks/admin/admin_menu.php';
include plugin_dir_path(GS_INDEX) . 'hooks/admin/admin_notice.php';
include plugin_dir_path(GS_INDEX) . 'hooks/admin/admin_test.php';
include plugin_dir_path(GS_INDEX) . 'hooks/admin/admin_tests.php';

# SCORES AND FIELD SUPPORT
include plugin_dir_path(GS_INDEX) . 'hooks/scores/score_survey_likert.php';
include plugin_dir_path(GS_INDEX) . 'hooks/field_support/field_survey_likert.php';
include plugin_dir_path(GS_INDEX) . 'hooks/field_support/field_html.php';


# FRONTEND
include plugin_dir_path(GS_INDEX) . 'hooks/frontend/shortcode.php';
include plugin_dir_path(GS_INDEX) . 'hooks/frontend/update_gscore.php';

# CHARTS
include plugin_dir_path(GS_INDEX) . 'hooks/charts/traffic_light.php';
include plugin_dir_path(GS_INDEX) . 'hooks/charts/normal_distribution.php';
include plugin_dir_path(GS_INDEX) . 'hooks/charts/radar_chart.php';

# REST API
include plugin_dir_path(GS_INDEX) . 'hooks/rest/rest_api_endpoints.php';
include plugin_dir_path(GS_INDEX) . 'hooks/rest/rest_get_evaluation.php';
include plugin_dir_path(GS_INDEX) . 'hooks/rest/rest_get_evaluations.php';
include plugin_dir_path(GS_INDEX) . 'hooks/rest/rest_get_form.php';
include plugin_dir_path(GS_INDEX) . 'hooks/rest/rest_get_forms.php';
include plugin_dir_path(GS_INDEX) . 'hooks/rest/rest_get_fields.php';
include plugin_dir_path(GS_INDEX) . 'hooks/rest/rest_get_test.php';
include plugin_dir_path(GS_INDEX) . 'hooks/rest/rest_get_tests.php';
include plugin_dir_path(GS_INDEX) . 'hooks/rest/rest_get_visualization.php';
include plugin_dir_path(GS_INDEX) . 'hooks/rest/rest_get_visualizations.php';
include plugin_dir_path(GS_INDEX) . 'hooks/rest/rest_get_groups.php';
include plugin_dir_path(GS_INDEX) . 'hooks/rest/rest_import.php';
include plugin_dir_path(GS_INDEX) . 'hooks/rest/rest_delete_evaluation.php';
include plugin_dir_path(GS_INDEX) . 'hooks/rest/rest_delete_test.php';
include plugin_dir_path(GS_INDEX) . 'hooks/rest/rest_append_subscale.php';
include plugin_dir_path(GS_INDEX) . 'hooks/rest/rest_put_group_result.php';
include plugin_dir_path(GS_INDEX) . 'hooks/rest/rest_delete_group_result.php';

# THIRD PARTY
include plugin_dir_path(GS_INDEX) . 'hooks/third_party/members.php';
include plugin_dir_path(GS_INDEX) . 'hooks/third_party/gravityforms_confirmation.php';
