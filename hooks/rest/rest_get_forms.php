<?php

add_filter('gravityscores_rest_get_forms', function ($atts) {
    $forms = \GFAPI::get_forms();

    $result = ['forms' => []];

    $accepted_keys = ['id', 'title', 'description'];

    return array_map(function ($form) use ($accepted_keys) {
        return array_filter((array) $form, function ($key) use ($accepted_keys) {
            return in_array($key, $accepted_keys);
        }, ARRAY_FILTER_USE_KEY);
    }, $forms);
});
