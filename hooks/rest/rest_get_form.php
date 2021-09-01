<?php

add_filter('gravityscores_rest_get_form', function ($atts) {
    $accepted_keys = ['id', 'title', 'description', 'fields'];
    $accepted_field_keys = ['id', 'label', 'type', 'description', 'inputType'];

    $form = (array) \GFAPI::get_form($atts['id']);

    $form = array_filter($form, function ($key) use ($accepted_keys) {
        return in_array($key, $accepted_keys);
    }, ARRAY_FILTER_USE_KEY);

    /*if (array_key_exists('fields', $form)) {

        $form['fields'] = array_map(function( $field ) use ( $accepted_field_keys ) {
            return array_filter( (array) $field, function($key) use ( $accepted_field_keys ){
                return in_array($key, $accepted_field_keys);
            }, ARRAY_FILTER_USE_KEY);

        }, $form['fields']);

    }*/

    return \apply_filters('gravityscores_form_rest_response', $form);
});
