<?php

use \gravityscores\Import as Import;

add_filter('gravityscores_rest_import', function ($atts) {

    $data = $atts->get_params();
    if (! array_key_exists('options', $data)){
        $data['options'] = [];
    }
    $evaluations_only = $data['options']['import_evaluations_only'] ?? false;
    $tests_only = $data['options']['import_tests_only'] ?? false;
    

    /*if (array_key_exists('visualizations', $data) && count($data['visualizations']) !== 0 && !$evaluations_only) {
        Import::visualizations($data, ['update' => $atts->get_method() == 'PUT']);
    }*/

    if (array_key_exists('tests', $data) && count($data['tests']) !== 0 && !$evaluations_only) {
        Import::tests($data, ['update' => $atts->get_method() == 'PUT']);
    }

    if (array_key_exists('evaluations', $data) && count($data['evaluations']) !== 0 && ! $tests_only) {
        Import::evaluations($data, ['update' => $atts->get_method() == 'PUT']);
    }
});
