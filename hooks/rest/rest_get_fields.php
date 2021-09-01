<?php

add_filter('gravityscores_rest_get_form_fields', function ($atts) {
    $accepted_keys = ['id', 'title', 'description', 'fields'];
    $accepted_field_keys = ['id', 'label', 'type', 'description', 'inputType'];

    $form = (array) \GFAPI::get_form($atts['id']);

    $form = array_filter($form, function ($key) use ($accepted_keys) {
        return in_array($key, $accepted_keys);
    }, ARRAY_FILTER_USE_KEY);

    $result = [];

    if (array_key_exists('fields', $form)) {
        foreach ($form['fields'] as $field) {
            $result = array_merge($result, \apply_filters('gravityscores_extend_field_rest_response', ['supported' => [], 'field' => $field])['supported']);
        }
    }

    return \apply_filters('gravityscores_fields_rest_response', $result);
});
