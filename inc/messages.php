<?php namespace gravityscores;

class MessageSystem
{
    public static $log_messages = [];
    public static $success_messages = [];
    public static $error_messages = [];

    public static function log($message)
    {
        array_push(self::$log_messages, $message);
    }

    public static function success($message)
    {
        array_push(self::$success_messages, $message);
    }

    public static function error($message)
    {
        array_push(self::$error_messages, $message);
    }

    public static function get_log_messages()
    {
        return self::$log_messages;
    }

    public static function get_success_messages()
    {
        return self::$success_messages;
    }

    public static function get_error_messages()
    {
        return self::$error_messages;
    }

    public static function get_all_messages()
    {
        return array_merge(self::get_log_messages(), self::get_success_messages(), self::get_error_messages());
    }

    public function is_empty()
    {
        return empty(self::$success_messages) && empty(self::$error_messages) && empty(self::$log_messages);
    }
}
