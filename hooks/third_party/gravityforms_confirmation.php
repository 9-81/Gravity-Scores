<?php
/**
 * GSCORE CONFIRMATION
 * Enables anonymus users to view their test results.
 *
 * Depending on the forms confirmation type in Gravity Forms, this either
 * appends a gscore parameter to the redirect url or adds a gscore hidden
 * field to the confirmation pages body.
 *
 * The gform parameter contains the information needed to let anonymus users
 * view their results.
 */

add_filter('gform_confirmation', function ($confirmation, $form, $entry, $ajax) {
    if (get_current_user_id() != 0) {
        return $confirmation;
    }

    // Encode data
    $base64_data =  base64_encode(str_pad($form['id'] . '::' . $entry['id'], 12, ' gscores'));

    // CONFIRMATION REDIRECT
    if (is_array($confirmation) && array_key_exists('redirect', $confirmation)) {

        // Check if redirect goes to the same page
        $url_relative_to_root = strpos($confirmation['redirect'], '/') === 0;
        $absolute_url_to_same_page = strpos($confirmation['redirect'], explode('//', get_site_url())[1]) !== false;
        
        if (!$url_relative_to_root && !$absolute_url_to_same_page) {
            return $confirmation;
        }
        
        // Modify redirect URL
        $connector = (strpos($confirmation['redirect'], '?') !== false) ? '&' : '?';
        $confirmation['redirect'] .= '?gscore=' . $base64_data;
    } else { // CONFIRMATION TEXT

        // Save the entry data in a hidden field.
        $confirmation = '<input type="hidden" id="gscore" name="gscore" value="' . $base64_data . '" style="display:none;" />' .  $confirmation;
    }

    return $confirmation;
}, 10, 4);
