<?php namespace gravityscores;

class Functions
{
    public static function is_plugin_active($plugin)
    {
        return in_array($plugin, (array) get_option('active_plugins', []));
    }

    public static function admin_page_import($section, $variables = [])
    {
        extract($variables);
        echo "<div class='wrap $section'>";
        include plugin_dir_path(GS_INDEX) . "tpl/admin_$section.php";
        echo '</div>';
    }

    public static function get_value($options, $key, $default)
    {
        return array_key_exists($key, $options) ? $options[$key] : $default;
    }

    public static function get_form_by_name($form_name)
    {
        foreach (\GFAPI::get_forms() as $gf_form) {
            if ($gf_form['title'] === $form_name) {
                return $gf_form;
            }
        }
        return false;
    }
}
