<?php namespace gravityscores;

class JavaScript
{
    public static function add_urls($script_name)
    {
        wp_localize_script($script_name, 'localURLs', [
            'home' => home_url(),
            'rest' => rest_url(),
            'gravityscores' => plugin_dir_url(GS_INDEX), 2
        ]);
    }

    public static function add_nonce($script_name, $identifier, $name = null)
    {
        wp_localize_script($script_name, $name ?? 'nonce', [wp_create_nonce($identifier)]);
    }
}
