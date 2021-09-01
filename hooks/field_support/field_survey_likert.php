<?php

add_filter('gravityscores_extend_field_rest_response', function ($data) {
    if ($data['field']['type'] != 'survey' || $data['field']['inputType'] != 'likert') {
        return $data;
    }


    // Create Preview
    $has_multiple_rows = count($data['field']['gsurveyLikertRows']) > 0;

    if ($has_multiple_rows) {
        foreach ($data['field']['gsurveyLikertRows'] as $current_index => $current_row) {
            $head_content = ($has_multiple_rows) ? '<th></th>' : '';
            foreach ($data['field']['choices'] as $choice) {
                $head_content .= '<th  style="padding:1em;">' . $choice['text'] . '</th>';
            }
            $head = "<thead><tr>$head_content</tr></thead>";

            $body_content = "";
            foreach ($data['field']['gsurveyLikertRows'] as $row) {
                $focus_class = ($row == $current_row) ? 'current' : '';
                
                $row_question = ($row == $current_row) ? $row['text'] : '...';

                $body_content .= '<tr class="'. $focus_class . '">';
                $body_content .= $has_multiple_rows ? '<td class="likert-question">' .  $row_question . '</td>' : '';
                
                foreach ($data['field']['choices'] as $choice) {
                    $body_content .= '<td style="padding:0.5rem;"><input type="radio" style="border:1px solid #555;" disabled/></td>';
                }
                $body_content .= '</tr>';
            }
            $body = "<tbody>$body_content</tbody>";
            
            $label = $data['field']['label'];

            array_push($data['supported'], [
                "field_id" => $data['field']['id'],
                "sub_question" => $current_index + 1,
                "label" => $data['field']['label'],
                "description" => $data['field']['description'],
                "type" => "survey_likert",
                "usable" => true,
                "preview" => "<strong style='color:grey;' >$label</strong><table style='text-align:center; color: grey; padding: 0.5em;'>$head$body</table"
            ]);
        }
    }

    return $data;
});
