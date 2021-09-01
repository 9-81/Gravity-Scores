<?php
/**
 * VERSIONCHECKS
 * The following dependencies are needed to run Gravity Scores.
 * This file makes sure, that it refueses to work if the dependencies are not met.
 */

use \gravityscores\MessageSystem as MessageSystem;
use \gravityscores\Functions as Functions;

global $wp_version;

if (array_key_exists('php_version', $options['requirements']) && version_compare(PHP_VERSION, $options['requirements']['php_version'], '<')) {
    MessageSystem::error('<strong>Invalid PHP version:</strong> Gravity Scores needs at least PHP version 7.2.');
}

if (array_key_exists('wordpress_version', $options['requirements']) && version_compare($wp_version, $options['requirements']['wordpress_version'], '<')) {
    MessageSystem::error('<strong>WordPress version outdated.</strong> Update WordPress to use Gravity Scores.');
}

if (array_key_exists('gravityscores', $options['requirements'])  && !Functions::is_plugin_active($options['requirements']['gravityscores'])) {
    MessageSystem::error('<strong>Gravity Forms not available.</strong> Install and activate it to use Gravity Scores.');
}
