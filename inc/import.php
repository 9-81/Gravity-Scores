<?php namespace gravityscores;

class Import
{
    public static $called_method;

    public static $model = [];

    public static $options = [];

    public static $tmp = [];

    public static function evaluations($data, $options = [])
    {
        $wpdb = self::init(__METHOD__, $data, $options);
    
        if (!array_key_exists('evaluations', self::$model)) {
            return false;
        }

        $result = true;
        $table = $wpdb->prefix . 'gs_evaluations';
        $insert_query = "INSERT INTO $table ( `title`, `visualization_id` ) VALUES ( %s, %d )";
        $delete_query = "DELETE FROM $table WHERE `id` = %d";
        $update_query = "UPDATE $table SET `title` = %s, `visualization_id` = %d WHERE `id` = %d";
       
        foreach (self::$model['evaluations'] as $evaluation_index => $evaluation) {

            if (array_key_exists('visualization_id', $evaluation)) {
                $visualization_id = $evaluation['visualization_id'];
            } else {
                // ToDo: Implement for no given visualization_id
            }

            if (!array_key_exists( 'title', $evaluation )) {
                $evaluation['title'] = 'unnamed evaluation';
            }

            $update = $options['update'] ?? false && $evaluation['id'] ?? null !== null;
            $delete = $evaluation['__delete__'] ?? false && $evaluation['id'] ?? null !== null;

            if ($delete) {
                $wpdb->query($wpdb->prepare($delete_query, $evaluation['id']));
                self::evaluation_subscales($evaluation, $evaluation['id'], array_merge($options, ['delete' => $delete]));
            } elseif ($update) {
                $wpdb->query($wpdb->prepare($update_query, $evaluation['title'], $visualization_id, $evaluation['id']));
                self::evaluation_subscales($evaluation, $evaluation['id'], array_merge($options, ['update' => $update]));
            } else {
                $wpdb->query($wpdb->prepare($insert_query, $evaluation['title'], $visualization_id));
                self::evaluation_subscales($evaluation, $wpdb->insert_id, $options);
            }

            
        }
        return true;
    }

    public static function evaluation_subscales($evaluation, $evaluation_id, $options = [])
    {
        global $wpdb;
        $table = $wpdb->prefix . 'gs_evaluation_subscale';
        $all_subscales = "SELECT subscale_id FROM $table WHERE `evaluation_id`=%d";
        $delete_query = "DELETE FROM $table WHERE `evaluation_id`=%d AND `subscale_id`=%d";
        $insert_query = "INSERT INTO $table (`evaluation_id`, `subscale_id`) VALUES ( %d, %d ) ";
        
        
        if ($options['update'] ?? false) {
            
            $db_subscale_ids = array_map( function( $subscale ) {
                return $subscale->subscale_id;
            }, $wpdb->get_results( $wpdb->prepare( $all_subscales, $evaluation_id )));
            
            foreach (array_unique(array_merge($evaluation['subscale_ids'], $db_subscale_ids))  as $subscale_id) {

                if( !in_array($subscale_id, $db_subscale_ids)) {
                    $wpdb->query( $wpdb->prepare( $insert_query, $evaluation_id, $subscale_id) ); 
                } elseif( !in_array($subscale_id, $evaluation['subscale_ids']) ) {
                    $wpdb->query( $wpdb->prepare($delete_query, $evaluation_id, $subscale_id));
                }
                 
            }
        } elseif ($options['delete'] ?? false) {
            // ToDo: On Delete
        } else {
            foreach ($evaluation['subscale_ids'] as $subscale_id) {
                $wpdb->query( $wpdb->prepare( $insert_query, $evaluation_id, $subscale_id ) );   
            }
        }


    }


    // DEPRECATED
    public static function visualizations($data, $options)
    {
        $wpdb = self::init(__METHOD__, $data, $options);
    

        if (!array_key_exists('visualization', self::$model)) {
            return false;
        }

        $result = true;

        $table = $wpdb->prefix . 'gs_visualizations';
        $insert_query = "INSERT INTO $table ( `name`, `min_subscales`, `max_subscales`, `type` ) VALUES ( %s, %d, %d, %s )";
        $delete_query = "DELETE FROM $table WHERE `id` = %d";
        $update_query = "UPDATE $table SET `title` = %s, `min_subscales` = %d, `max_subscales` = %d, `type` = %s  WHERE `id` = %d";
        $select_id = "SELECT `id` FROM $table WHERE `name` = %s";
        
        if (array_key_exists('id', self::$model['visualization']) && self::$model['visualization']['id'] !== NULL){
            
        }
        /*
        foreach (self::$model['evaluations'] as $visualization_index => $visualization) {
            $update = $options['update'] ?? false && $visualization['id'] ?? null !== null;
            $delete = $visualization['__delete__'] ?? false && $visualization['id'] ?? null !== null;

            if ($delete) {
                $wpdb->query($wpdb->prepare($delete_query, $visualization['id']));
            } elseif ($update) {
                $wpdb->query($wpdb->prepare($update_query, $visualization['title'], $visualization['min_subscales'], $visualization['max_subscales'], $visualization['type'], $visualization['id']));
                $visualization_id = $visualization['id'];
            } else {
                $visualization_id = $wpdb->get_var($wpdb->prepare($select_id, $visualization['name']));

                if ($visualization_id !== null) {
                    $wpdb->query($wpdb->prepare($insert_query, $visualization['title'], $visualization['min_subscales'], $visualization['max_subscales'], $visualization['type']));
                    $visualization_id = $wpdb->insert_id;
                }
            }

            if (! $delete) {
                array_push(self::$tmp['visualization_ids'], $visualization_id);
            }
        }*/

        return true;
    }

    public static function tests($data, $options = [])
    {
        $wpdb = self::init(__METHOD__, $data, $options);
        
        if (!array_key_exists('tests', self::$model)) {
            return false;
        }

        $result = true;

        $table = $wpdb->prefix . 'gs_tests';
        $insert_query = "INSERT INTO $table ( `form_id` ) VALUES ( %d )";
        $delete_query = "DELETE FROM $table WHERE `id` = %d";
        $update_query = "UPDATE $table SET `form_id` = %d WHERE `id` = %d";
        
        foreach (self::$model['tests'] as $test_index => $test) {
            if (array_key_exists('form_id', $test)) {
                $form_id = $test['form_id'];
            } else {
                foreach (\GFAPI::get_forms() as $gf_form) {
                    if ($gf_form['title'] === $test['form_title']) {
                        $form_id = $gf_form['id'];
                    }
                }
            }

            if (!isset($form_id) || empty($form_id)) {
                throw new \Exception('The Gravity Forms form ' . $test['form_title'] . ' is not available.');
            }

            $id = $test['id'] ?? null;
            $update = self::$options['update'] ?? false  && $id !== null;
            $delete = $test['__delete__'] ?? false && $id !== null ;
            
            if ($delete) {
                $wpdb->query($wpdb->prepare($delete_query, $test['id']));
            } elseif ($update) {
                if ($test['updated'] ?? false){
                    $wpdb->query($wpdb->prepare($update_query, $form_id, $test['id']));
                }
                $test_id = $test['id'];
            } else {
                $wpdb->query($wpdb->prepare($insert_query, $form_id));
                $test_id = $wpdb->insert_id;
            }

            $result &= self::subscales($test_index, $test_id, $delete, $options);
        }
        return self::result(__METHOD__, $result);
    }

    public static function subscales($test_index, $test_id, $parent_delete, $options = [])
    {
        global $wpdb;

        $result = true;
        
        $subscales = self::$model['tests'][$test_index]['subscales'];
        $table = $wpdb->prefix . 'gs_subscales';
        $update_query = "UPDATE $table SET `test_id` = %d, `name` = %s, `description` = %s WHERE `id` = %d";
        $delete_query = "DELETE FROM $table WHERE `id` = %d";
        $insert_query = "INSERT INTO $table ( `test_id`, `name`, `description` ) VALUES ( %d, %s, %s )";
        
        foreach ($subscales as $subscale_index => $subscale) {
            $id = $subscale['id'] ?? null;
            $update = self::$options['update'] ?? false  && $id !== null;
            $delete = ($subscale['__delete__'] ?? false || $parent_delete) && $id !== null ;

            if ($delete) {
                $wpdb->query($wpdb->prepare($delete_query, $subscale['id']));
            } elseif ($update) {
                if ($subscale['updated'] ?? false) {
                    $wpdb->query($wpdb->prepare($update_query, $subscale['test_id'], $subscale['name'], $subscale['description'], $id));
                }
                $subscale_id = $subscale['id'];
            } else {
                $wpdb->query($wpdb->prepare($insert_query, $test_id, $subscale['name'], $subscale['description']));
                $subscale_id = (int) $wpdb->insert_id;
            }
            
            $result &= self::group_results($test_index, $subscale_index, $subscale_id, $delete, $options);
            $result &= self::evaluables($test_index, $subscale_index, $subscale_id, $delete, $options);
        }

        return self::result(__METHOD__, $result);
    }

    public static function group_results($test_index, $subscale_index, $subscale_id, $parent_delete, $options)
    {
        global $wpdb;

        $result = true;

        $group_results = self::$model['tests'][$test_index]['subscales'][$subscale_index]['group_results'];
        
        $table = $wpdb->prefix . 'gs_group_results';
        $update_query = "UPDATE $table SET `subscale_id` = %d, `group_id` = %d, `data` = %s WHERE `id` = %d";
        $delete_query = "DELETE FROM $table WHERE `id` = %d";
        $insert_query = "INSERT INTO $table ( `subscale_id`, `group_id`, `data` ) VALUES ( %d, %d, %s )";

        self::$tmp[__METHOD__] = [];


        foreach ($group_results as $group_result) {
            $id = $group_result['id'] ?? null;
            $update = $options['update'] ?? false;
            $delete = $evaluable['__deleted__'] ?? false ;

            $groups_table = $wpdb->prefix . 'gs_groups';

            if (count(self::$tmp[__METHOD__]) == 0) {
                foreach ($wpdb->get_results("SELECT * FROM $groups_table") as $group) {
                    self::$tmp[__METHOD__][$group->name] = $group->id;
                }
            }

            if (!array_key_exists($group_result['group'], self::$tmp[__METHOD__])) {
                $wpdb->query($wpdb->prepare("INSERT INTO $groups_table (`name`) VALUES ( %s )", $group_result['group']));
                self::$tmp[__METHOD__][$group_result['group']] = $wpdb->insert_id;
            }

            $group_id = self::$tmp[__METHOD__][$group_result['group']];

            if ($delete) {
                $wpdb->query($wpdb->prepare($delete_query, $group_id));
            } elseif ($update && ($group_result['__updated__'] ?? false)) {
                $wpdb->query($wpdb->prepare($update_query, $subscale_id, $group_id, json_encode($group_result['data'] ?? (object) []), $group_result['id']));
                $group_result_id = $group_result['id'];
            } elseif( !$update || ($group_result['__inserted__'] ?? false) ) {
                $wpdb->query($wpdb->prepare($insert_query, $subscale_id, $group_id, json_encode($group_result['data'] ?? (object) [])));
                $group_result_id = $wpdb->insert_id;
            }
        }

        return true;
    }

    public static function evaluables($test_index, $subscale_index, $subscale_id, $parent_delete, $options=[])
    {
        global $wpdb;

        $result = true;

        $evaluables = self::$model['tests'][$test_index]['subscales'][$subscale_index]['evaluables'];

        $table = $wpdb->prefix . 'gs_evaluables';
        $update_query = "UPDATE $table SET `subscale_id` = %d, `type` = %s, `field_id` = %d, `sub_question` = %d, `weight` = %f WHERE `id` = %d";
        $delete_query = "DELETE FROM $table WHERE `id` = %d";
        $insert_query = "INSERT INTO $table ( `subscale_id`, `type`, `field_id`, `sub_question`, `weight` ) VALUES ( %d, %s, %d, %d, %f )";

        foreach ($evaluables as $evaluable) {
            $id = $evaluable['id'] ?? null;
            $update = $options['update'] ?? false;
            $delete = $evaluable['__deleted__'] ?? false ;
       
            if ($delete) {
                $wpdb->query($wpdb->prepare($delete_query, $id));
            } elseif ($update && ($evaluable['__updated__'] ?? false)) {
                $wpdb->query($wpdb->prepare($update_query, $subscale_id, $evaluable['type'], $evaluable['field_id'], $evaluable['sub_question'], $evaluable['weight'], $id));
                $evaluable_id = $id;
            } elseif( !$update || ($evaluable['__inserted__'] ?? false) ) {
                $wpdb->query($wpdb->prepare($insert_query, $subscale_id, $evaluable['type'], $evaluable['field_id'], $evaluable['sub_question'], $evaluable['weight']));
                $evaluable_id = $wpdb->insert_id;
            }
        }

        return true;
    }

    public static function result($method, $result)
    {
        if (self::$called_method === $method) {
            self::$called_method = null;
            self::$model = [];
            self::$tmp = [];
        }

        return $result;
    }

    public static function init($method, $data, $options)
    {
        global $wpdb;
        
        if (self::$called_method === null) {
            self::$called_method = $method;

            switch (gettype($data)) {
                case 'string':
                    self::$model = json_decode($data, true);
                break;
                case 'array':
                    self::$model = $data;
                break;
            }

            self::$options = $options;
        }
    

        return $wpdb;
    }
}
