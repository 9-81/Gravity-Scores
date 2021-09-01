<?php

register_activation_hook(GS_INDEX, function () use ($options) {

    // USER CAPABILITIES SETUP
    foreach ($GLOBALS['wp_roles']->role_objects as $role) {
        foreach (array_keys($options['capabilities']) as $capability) {
            if ($role->has_cap($capability)) {
                continue;
            }
        
            $role->add_cap($capability, $role->has_cap('edit_pages'));
        }
    }

    // DATABASE SETUP
    global $wpdb;

    $querys = [
        "CREATE TABLE IF NOT EXISTS __PREFIX__tests (
            `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `form_id` INT NOT NULL
        )",
        "CREATE TABLE IF NOT EXISTS __PREFIX__visualizations (
            `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(127),
            `min_subscales` INT, 
            `max_subscales` INT, 
            `type` VARCHAR(63)
        )",
        "CREATE TABLE IF NOT EXISTS __PREFIX__evaluations (
            `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `title` VARCHAR(127) NOT NULL, 
            `visualization_id` INT,
            CONSTRAINT `FK_EvaluationsVisualizations` FOREIGN KEY (`visualization_id`) REFERENCES __PREFIX__visualizations(`id`) ON DELETE CASCADE
        )",
        "CREATE TABLE IF NOT EXISTS __PREFIX__subscales (
            `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `test_id` INT, name VARCHAR(127),
            `description` TEXT,
            CONSTRAINT `FK_SubscalesTest` FOREIGN KEY (`test_id`) REFERENCES __PREFIX__tests(`id`) ON DELETE CASCADE
        )",
        "CREATE TABLE IF NOT EXISTS __PREFIX__evaluation_subscale (
            `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `evaluation_id` INT,
            `subscale_id` INT,
            CONSTRAINT `FK_EvaluationSubscalesEvaluation` FOREIGN KEY (`evaluation_id`) REFERENCES __PREFIX__evaluations(`id`) ON DELETE CASCADE,
            CONSTRAINT `FK_EvaluationSubscalesSubscale` FOREIGN KEY (`subscale_id`) REFERENCES __PREFIX__subscales(`id`) ON DELETE CASCADE
        )",
        "CREATE TABLE IF NOT EXISTS __PREFIX__groups (
            `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY, 
            `name` VARCHAR(63)
        )",
        "CREATE TABLE IF NOT EXISTS __PREFIX__group_results (
            `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `subscale_id` INT,
            `group_id` INT,
            `data` TEXT,
            CONSTRAINT `FK_Group_ResultsSubscale` FOREIGN KEY (`subscale_id`) REFERENCES __PREFIX__subscales(`id`) ON DELETE CASCADE,
            CONSTRAINT `FK_Group_ResultsGroup` FOREIGN KEY (`group_id`) REFERENCES __PREFIX__groups(`id`) ON DELETE CASCADE
        )",
        "CREATE TABLE IF NOT EXISTS __PREFIX__evaluables (
            `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `subscale_id` INT, type VARCHAR(31),
            `field_id` INT, sub_question INT,
            `weight` FLOAT,
            CONSTRAINT `FK_EvaluablesSubscales` FOREIGN KEY (`subscale_id`) REFERENCES __PREFIX__subscales(`id`) ON DELETE CASCADE
        )",
        "CREATE TABLE IF NOT EXISTS __PREFIX__binary_answers (
            `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `evaluable_id` INT, 
            `data` TEXT,
            CONSTRAINT `FK_AnswersEvaluables` FOREIGN KEY (`evaluable_id`) REFERENCES __PREFIX__evaluables(`id`) ON DELETE CASCADE
        )"
    ];


    foreach ($querys as $query) {
        $wpdb->query(str_replace('__PREFIX__', $GLOBALS['wpdb']->prefix . 'gs_', $query));
    }
    

    $charts = apply_filters('gs_register_chart', []);

    $visualizations_table = $GLOBALS['wpdb']->prefix . 'gs_visualizations';

    foreach ($charts as $chart) {
        $wpdb->insert($visualizations_table, [
            'name' => $chart['name'],
            'min_subscales' => $chart['min_subscales'],
            'max_subscales' => $chart['max_subscales'],
            'type' => null
        ]);
    }
});
