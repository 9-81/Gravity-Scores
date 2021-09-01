<?php

use \gravityscores\Export as Export;
use \gravityscores\Score as Score;

add_filter('gravityscores_rest_get_test', function ($atts) {
    $options = ['clean' => true, 'preserve_ids' => true];

    $data = Export::tests([$atts['id']], $options);

    $entry_id = null;

    if (array_key_exists('option', $_GET) && ($_GET['option'] == 'scored')) {
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
            
            if ($entry_id === null) {
                return [];
            }
            $data['tests'][$test_index]['entry_id'] = $entry_id;
        }
        
        $scored = Score::export($data);
        
        return $scored;
    }
    
    return $data;
});
