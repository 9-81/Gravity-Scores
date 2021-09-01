<?php namespace gravityscores;

/**
 * # EXPORT
 *
 * Exports data from the database into an object or a json-string.
 *
 * ## Requirements
 *
 * The component depends on:
 *
 * - `hook/activation.php`
 * - `inc/database.php`
 *
 * ## Usage
 *
 * You can use the Export class to retrive data from the database. The following
 * example show how you can use it to get json-data for the evaluations 1, 3, 5
 * including all dependencies except the linked evaluables.
 *
 * ```
 * $options = [
 *     'evaluables' => false,
 *      'json' => true
 * ];
 * Export::evaluations([1,3,5], $options);
 * ```
 *
 * There are options for each Function that can be used to twek the functions behavior.
 * Possible options are:
 * - "json" returns json-string.
 * - "clean" cleans the data, deletes real object id's and insteadadds references
 *    to the position in the returned data structure. `json` is cleaned by default.
 * - "preserve_ids" preserves the database ids when the data is cleaned.
 * You can also pass something link [FUNCTION_NAME => false]. As a result of this
 * the corresponding method will do nothing exept returning an empty array.
 * This way you can control, which parts of the data you want to export.
 *
 * The resulting data can be filtered. using the `gravityscores_export_results` filter.
 */

class Export
{
    private static $model = [];

    private static $id_mappers = [];

    private static $tmp = [];

    private static $called_method = null;

    public static function evaluations($ids, $options = [])
    {
        $wpdb = self::init(__METHOD__);

        if (! ($options['evaluations'] ?? true) || empty($ids)) {
            return self::results([], __METHOD__, $options);
        }

        $result = Database::multi_select('evaluations', $ids);
        $ss_connect = Database::multi_select('evaluation_subscale', $ids, '*', 'evaluation_id');
        
        if (empty($result)) {
            return self::results([], __METHOD__, $options);
        }

        self::$model['evaluations'] = [];
        self::$tmp[__METHOD__] = [];
        $connections = [];

        foreach ($result as $evaluation) {
            array_push(self::$tmp[__METHOD__], $evaluation->visualization_id);

            $subscale_con = array_filter($ss_connect, function ($con) use ($evaluation) {
                return $evaluation->id === $con->evaluation_id;
            });
            
            array_push(self::$model['evaluations'], [
                'id' => $evaluation->id,
                'title' => $evaluation->title,
                'visualization_id' => $evaluation->visualization_id,
                'subscale_ids' => array_map(function ($con) {
                    return $con->subscale_id;
                }, $subscale_con)
            ]);

            if (! array_key_exists('tests', self::$model)) {
                // Find the tests, related to the current evaluation
                $con_table = $wpdb->prefix . 'gs_evaluation_subscale';
                $subscales_table = $wpdb->prefix . 'gs_subscales';
                $test_id_query = "SELECT DISTINCT ss.test_id AS test_id ".
                                 "FROM $con_table AS con " .
                                 "LEFT JOIN $subscales_table AS ss " .
                                     "ON con.subscale_id = ss.id " .
                                 "WHERE con.evaluation_id = %d";
                
                $related_test_ids = array_map(function ($result) {
                    return $result->test_id;
                }, $wpdb->get_results($wpdb->prepare($test_id_query, $evaluation->id)));
            }
        }

        self::visualizations(array_unique(self::$tmp[__METHOD__]), $options);
        self::tests(array_unique(isset($related_test_ids) ? $related_test_ids : []), $options);
        self::$tmp[__METHOD__] = null;

        

        return self::results(self::$model['evaluations'], __METHOD__, $options);
    }

    public static function visualizations($ids, $options = [])
    {
        self::init(__METHOD__);

        if (! ($options['visualizations'] ?? true) || empty($ids)) {
            return [];
        }

        $results = Database::multi_select('visualizations', $ids);

        self::$model['visualizations'] = [];

        self::$tmp[__METHOD__] = [];

        foreach ($results as $visualization) {
            if (in_array($visualization->id, self::$tmp[__METHOD__])) {
                continue;
            }

            array_push(self::$tmp[__METHOD__], $visualization->id);

            array_push(self::$model['visualizations'], [
                'id' => $visualization->id,
                'name' => $visualization->name,
                'type' => $visualization->type,
                'min_subscales' => $visualization->min_subscales,
                'max_subscales' => $visualization->max_subscales
            ]);
        }

        self::$tmp[__METHOD__] = null;

        return self::results(self::$model['visualizations'], __METHOD__, $options);
    }

    public static function tests($ids, $options = [])
    {
        self::init(__METHOD__);

        if (! ($options['tests'] ?? true) || empty($ids)) {
            return [];
        }

        self::$model['tests'] = [];

        $tests = Database::multi_select('tests', $ids);

        foreach ($tests as $test) {
            $related_subscale_ids = array_map(function ($subscale) {
                return $subscale->id;
            }, Database::multi_select('subscales', [$test->id], 'id', 'test_id'));

            $test = ['id' => $test->id, 'form_id' => $test->form_id, 'form_title' => \GFAPI::get_form($test->form_id)['title']];

            if ($test['form_title'] === null) {
                MessageSystem::error('The Test with `id` = 1 depends on a form with `form_id` = ' . $test['form_id'] . ' but there is no such form in Gravity Forms.');
            }

            if ($options['subscales'] ?? true) {
                $test['subscales'] = self::subscales($related_subscale_ids, $options);
            }

            array_push(self::$model['tests'], $test);
        }

        return self::results(self::$model['tests'], __METHOD__, $options);
    }

    public static function subscales($ids, $options = [])
    {
        self::init(__METHOD__);

        if (! ($options['subscales'] ?? true) || empty($ids)) {
            return [];
        }

        $results = Database::multi_select('subscales', $ids);

        $subscales = [];

        foreach ($results as $subscale) {
            $related_evaluable_ids = array_map(function ($evaluable) {
                return $evaluable->id;
            }, Database::multi_select('evaluables', [$subscale->id], 'id', 'subscale_id'));


            $related_group_result_ids = array_map(function ($group_result) {
                return $group_result->id;
            }, Database::multi_select('group_results', [$subscale->id], 'id', 'subscale_id'));
            
            $subscale = [
                'id' => $subscale->id,
                'test_id' => $subscale->test_id,
                'name' => $subscale->name,
                'description' => $subscale->description
            ];

            if ($options['group_results'] ?? true) {
                $subscale['group_results'] = self::group_results($related_group_result_ids, $options);
            }

            if ($options['evaluables'] ?? true) {
                $subscale['evaluables'] = self::evaluables($related_evaluable_ids, $options);
            }

            array_push($subscales, $subscale);
        }
        
        return self::results($subscales, __METHOD__, $options);
    }

    public static function group_results($ids, $options = [])
    {
        $wpdb = self::init(__METHOD__);

        if (! ($options['group_results'] ?? true) || empty($ids)) {
            return [];
        }

        $group_results = [];

        if (! isset(self::$tmp[__METHOD__])) {
            self::$tmp[__METHOD__] = [];
            $group_table = $wpdb->prefix . "gs_groups";
            foreach ($wpdb->get_results("SELECT * FROM $group_table") as $group) {
                self::$tmp[__METHOD__][$group->id] = $group->name;
            }
        }
            
        $results = Database::multi_select('group_results', $ids);

        $group_ids = array_map(function ($group_result) {
            return $group_result->group_id;
        }, $results);

        foreach ($results as $group_result_data) {
            $group_result = [
                'id' => $group_result_data->id,
                'data' => json_decode($group_result_data->data, true),
                'group' => self::$tmp[__METHOD__][strval($group_result_data->group_id)]
            ];

            array_push($group_results, $group_result);
        }

        return self::results($group_results, __METHOD__, $options);
    }

    public static function evaluables($ids, $options = [])
    {
        self::init(__METHOD__);

        if (! ($options['evaluables'] ?? true) || empty($ids)) {
            return [];
        }

        $results = Database::multi_select('evaluables', $ids);

        $evaluables = [];

        foreach ($results as $evaluable) {
            array_push($evaluables, [
                'id' => $evaluable->id,
                'type' => $evaluable->type,
                'field_id' => $evaluable->field_id,
                'sub_question' => $evaluable->sub_question,
                'weight' => $evaluable->weight
            ]);
        }

        return self::results($evaluables, __METHOD__, $options);
    }

    public static function clean($data, $options = [])
    {
        $options['temp_evaluations'] = false;
        $options['temp_visualizations'] = false;
        
        if (! array_key_exists('tests', $data)) {
            $data['tests'] = [];
        }

        if (! array_key_exists('evaluations', $data)) {
            $data['evaluations'] = [];
        }

        if (! array_key_exists('visualizations', $data)) {
            $data['visualizations'] = [];
        }
    
        // Set Visualization Index for Evaluations
        foreach ($data['visualizations'] as $index => $visualization) {
            for ($i = 0; $i < count($data['evaluations']); $i++) {
                if ($visualization['id'] === $data['evaluations'][$i]['visualization_id']) {
                    $data['evaluations'][$i]['visualization_index'] = $index;
                }
            }
        }

        for ($i = 0; $i < count($data['evaluations']); $i++) {
            foreach ($data['tests'] as $test_index => $test) {
                if (array_key_exists('subscales', $test)) {
                    continue;
                }

                foreach ($test['subscales'] as $subscale_index => $subscale) {
                    if (in_array($subscale['id'], $data['evaluations'][$i]['subscale_ids'])) {
                        $data['evaluations'][$i]['subscales'] =[
                            'test_index' => $test_index,
                            'subscale_index' => $subscale_index
                        ];
                    }
                }
            }
        }

        if (array_key_exists('preserve_ids', $options) && $options['preserve_ids']) {
            return $data;
        }
        

        // Delete `id` and `visualization_id` and `subscale_ids` for all Evaluations
        for ($i = 0; $i < count($data['evaluations']); $i++) {
            unset(
                $data['evaluations'][$i]['id'],
                $data['evaluations'][$i]['visualization_id'],
                $data['evaluations'][$i]['subscale_ids']
            );
        }

        // Delete `id` for all Visualizations
        for ($i = 0; $i < count($data['visualizations']); $i++) {
            unset($data['visualizations'][$i]['id']);
        }

        // Delete `id` for all Tests
        for ($i = 0; $i < count($data['tests']); $i++) {
            unset($data['tests'][$i]['id']);
            
            // Delete `id` for all Subscales
            for ($j = 0; $j < count($data['tests'][$i]['subscales']); $j++) {
                unset($data['tests'][$i]['subscales'][$j]['id'], $data['tests'][$i]['subscales'][$j]['test_id']);
                // Delete `id` for all group results
                for ($k = 0; $k < count($data['tests'][$i]['subscales'][$j]['group_results']); $k++) {
                    unset($data['tests'][$i]['subscales'][$j]['group_results'][$k]['id']);
                }

                // Delete `id` for all evaluables
                for ($k = 0; $k < count($data['tests'][$i]['subscales'][$j]['evaluables']); $k++) {
                    unset($data['tests'][$i]['subscales'][$j]['evaluables'][$k]['id']);
                }
            }
        }

        return $data;
    }

    public static function to_json($data, $options)
    {
        return json_encode($data) ;
    }

    private static function init($method, $params = null)
    {
        if (self::$called_method === null) {
            self::$called_method = $method;
            self::$model = [];
        }
        
        return $GLOBALS['wpdb'];
    }

    private static function results($results, $method, $options = [])
    {
        if (self::$called_method === null) {
            throw new \Error('The called method pointer was not set in ' . __FILE__ . ':' . __LINE__);
        }
        
        if (!empty(self::$model) && self::$called_method === $method) {
            return self::flush(\apply_filters('gravityscores_export_results', self::$model), $options);
        } elseif (self::$called_method === $method) {
            return self::flush(\apply_filters('gravityscores_export_results', $results), $options);
        }

        return $results;
    }

    private static function flush($model, $options = [])
    {
        self::$called_method = null;

        // Checks weather the data should be cleaned. Defaults to "true" if json is "true"
        if (($options['clean'] ?? Functions::get_value($options, 'json', false))) {
            $model = self::clean($model, $options);
        }

        // Clear all data if it has been changed
        self::$tmp = [];
        self::$called_method = null;

        return ($options['json'] ?? false) ? self::to_json($model, $options) : $model;
    }
}
