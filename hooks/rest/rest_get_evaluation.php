<?php

use \gravityscores\Export as Export;
use \gravityscores\Score as Score;

add_filter('gravityscores_rest_get_evaluation', function ($atts) {
    $options = ['clean' => true, 'preserve_ids' => true];

    $data = Export::evaluations([$atts['id']], $options);

    if (array_key_exists('option', $_GET) && ($_GET['option'] == 'scored')) {
        
        // return null if the userid and gscores are not set - and therefore a gravity forms user entry can not be found.
        if (get_current_user_id() == 0 && (!isset($_GET['gscores']) || empty($_GET['gscores']))) {
            return null;
        }

        foreach ($data['tests'] as $test_index => $test) {
            if (get_current_user_id() != 0) {
                $search_criteria = ['status' => 'active', 'field_filters' => [['key' => 'created_by', 'value' => get_current_user_id()]]];
                $entry_ids = \GFAPI::get_entry_ids($test['form_id'], $search_criteria);
                $entry_id = current($entry_ids);
            } elseif ($_GET['gscores'] ?? false) {
                $gscores = json_decode(base64_decode($_GET['gscores']));
                $index = array_search($test['form_id'], explode(':', $gscores['form_ids']));
                $entry_id = $gscores['entry_ids'][$index];
            }

            $data['tests'][$test_index]['entry_id'] = $entry_id;
        }

        try {
            $scored = Score::export($data);
            return $scored;
        } catch (Exception $e) {
            return null;
        }
    }
    
    return $data;
});
