<?php namespace gravityscores;

class Database
{
    public static function multi_select($table, $compare_values, $select_column = '*', $compare_column = 'id')
    {
        global $wpdb;

        $prefixed_table = $wpdb->prefix . 'gs_' . $table;

        $placeholder = Functions::get_value([
            'integer' => '%d',
            'double' => '%f',
            'string' => '%s'
        ], gettype($compare_values[0]), '%s');
        
        $placeholders = implode(', ', array_fill(0, count($compare_values), $placeholder));
        $query = "SELECT $select_column FROM `$prefixed_table` WHERE `$compare_column` IN ( $placeholders )";
        $stmt = call_user_func_array([$wpdb, 'prepare'], array_merge([$query], $compare_values));
        
        return $wpdb->get_results($stmt);
    }

    public static function multi_insert($table, $data)
    {
        global $wpdb;
        $prefixed_table = $wpdb->prefix . 'gs_' . $table;
        $columns_str = implode(', ', array_keys($data[0]));

        $format = array_map(function ($value) {
            switch ($value) {
                case 'integer':
                    return '%d';
                case 'double':
                    return '%f';
                default:
                    return '%s';
            }
        }, $data[0]);

        return array_map(function ($data) use ($prefixed_table, $format) {
            $wpdb->insert($prefixed_table, $data, $format);
            return $wpdb->insert_id;
        }, $data);
    }
}
