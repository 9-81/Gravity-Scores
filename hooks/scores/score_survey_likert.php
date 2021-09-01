<?php

add_filter('gravityscores_score_evaluable', function ($data) {
    
    // HEADER
    if (!in_array($data['evaluable']['type'], ['survey_likert'])) {
        return $data;
    }
    
    extract($data); // Extracts variables `$evaluable`, `$field`, `$entry`

    // COLLECT DATA
    $max_value = max(array_map(function ($choice) {
        return $choice['score'];
    }, $field['choices']));

    $inverter = (0 > $evaluable['weight']) ? -1 : 1;
    
    $data_identifier = explode(':', $entry[ (string) $evaluable['field_id'] . '.' . $evaluable['sub_question'] ]);

    if (count($data_identifier) == 2) {
        $value = current(array_filter($field['choices'], function ($choice) use ($data_identifier) {
            return $choice['value'] === $data_identifier[1];
        }))['score'];
    } else {
        $value = 0;
    }
    
    $score = ($inverter * $evaluable['weight']) * ((($max_value + 1 + ($inverter * $value)) % ($max_value + 1)));

    
    if (array_key_exists('score', $evaluable)) {
        $data['evaluable']['score'] += $score;
    } else {
        $data['evaluable']['score'] = $score;
    }

    return $data;
});
