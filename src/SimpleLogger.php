<?php

namespace calguy1000\logger;

final class SimpleLogger
{
    private $_logger;
    private static $_instance;

    protected function __construct($filename,$max_age,$max_size,$keepmax)
    {
        $this->_logger = new Logger($filename,$max_age,$max_size,$keepmax);
    }

    public static function init($filename, $max_age = null, $max_size = null, $keepmax = 10)
    {
        if( !$max_age ) $max_age = 30 * 24;
        if( !$max_size) $max_size = 5 * 1024;

        self::$_instance = new self($filename,$max_age,$max_size,$keepmax);
    }

    public static function &get_instance()
    {
        if( !self::$_instance ) throw new \LogicException('Call to '.__METHOD__.' Before initialization');
        return self::$_instance;
    }

    public static function debug($msg,$section = null,$item = null)
    {
        self::get_instance()->_logger->debug($msg,$section,$item);
    }

    public static function info($msg,$section = null,$item = null)
    {
        self::get_instance()->_logger->info($msg,$section,$item);
    }

    public static function warn($msg,$section = null,$item = null)
    {
        self::get_instance()->_logger->warn($msg,$section,$item);
    }

    public static function error($msg,$section = null,$item = null)
    {
        self::get_instance()->_logger->error($msg,$section,$item);
    }

} // end of class
