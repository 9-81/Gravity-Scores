<?php

add_filter('gravityscores_extend_field_rest_response', function ($data) {
    if (($data['field']['type'] != 'html' &&  $data['field']['inputType'] != '') || (!isset($data['field']['content']) || empty($data['field']['content']))) {
        return $data;
    }

    array_push($data['supported'], [
        "field_id" => $data['field']['id'],
        "sub_question" => null,
        "label" => $data['field']['label'],
        "description" => $data['field']['description'],
        "type" => "html",
        "usable" => false,
        "preview" => $data['field']['content']
    ]);
        

    return $data;
});
