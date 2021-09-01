<?php

    
if (\gravityscores\Functions::is_plugin_active('members/members.php')) {
    add_action('members_register_cap_groups', function () {
        members_register_cap_group('gravityscores', [
            'label'    => __('Gravity Scores'),
            'icon'     => 'dashicons-chart-bar',
            'priority' => 10
        ]);
    });
    
    
    add_action('members_register_caps', function () use ($options) {
        foreach ($options['capabilities'] as $capability => $capability_label) {
            members_register_cap($capability, [ 'label' => __($capability_label), 'group' => 'gravityscores']);
        }
    });
}
