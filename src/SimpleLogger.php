<?php

/**
 * An auxillary logging mechanism.
 *
 * @package logger
 */

namespace calguy1000\logger;

/**
 * A singleton class to aide in logging for an application.
 *
 * @package logger
 * @author Robert Campbell <calguy1000@gmail.com>
 * @copyright 2015
 * @license LGPL2.1
 */
class SimpleLogger implements Logger
{
    /**
     * @ignore
     */
    private $_logger;

    /**
     * @ignore
     */
    private static $_instance;

    /**
     * @ignore
     */
    protected function __construct($filename,$max_age,$max_size,$keepmax)
    {
        $this->_logger = new Logger($filename,$max_age,$max_size,$keepmax);
    }

    /**
     * Initialize the logging system.
     *
     * @see Logger::init()
     * @param string $filename The destination filename.
     * @param int $max_age_h The maximum age of a log file before rotation.  Default is 30*24.  Max is 60*24.
     * @param int $max_size_kb The maximum size of a file before rotation (kilobytes).  Default is 10*1024.  Max is 50*1024
     * @param int keepmax The maximum number of files to keep.  Default is 10.
     * @throws \LogicException
     * @throws \RuntimeException
     */
    public static function init($filename, $max_age = null, $max_size = null, $keepmax = 10)
    {
        if( !$max_age ) $max_age = 30 * 24;
        if( !$max_size) $max_size = 10 * 1024;

        self::$_instance = new self($filename,$max_age,$max_size,$keepmax);
    }

    /**
     * Return the reference to the only instance of this class.
     *
     * @return SimpleLogger
     * @throws \LogicException
     */
    public static function &get_instance()
    {
        if( !self::$_instance ) throw new \LogicException('Call to '.__METHOD__.' Before initialization');
        return self::$_instance;
    }

    /**
     * A convenience function to add a debug message to the log file.
     *
     * @param string $msg The output message.
     * @param string $section An optional section key
     * @param int $item An optional item key
     * @throws \LogicException
     */
    public static function debug($msg,$section = null,$item = null)
    {
        self::get_instance()->_logger->debug($msg,$section,$item);
    }

    /**
     * A convenience function to add an info message to the log file.
     *
     * @param string $msg The output message.
     * @param string $section An optional section key
     * @param int $item An optional item key
     * @throws \LogicException
     */
    public static function info($msg,$section = null,$item = null)
    {
        self::get_instance()->_logger->info($msg,$section,$item);
    }

    /**
     * A convenience function to add a warning message to the log file.
     *
     * @param string $msg The output message.
     * @param string $section An optional section key
     * @param int $item An optional item key
     * @throws \LogicException
     */
    public static function warn($msg,$section = null,$item = null)
    {
        self::get_instance()->_logger->warn($msg,$section,$item);
    }

    /**
     * A convenience function to add an error message to the log file.
     *
     * @param string $msg The output message.
     * @param string $section An optional section key
     * @param int $item An optional item key
     * @throws \LogicException
     */
    public static function error($msg,$section = null,$item = null)
    {
        self::get_instance()->_logger->error($msg,$section,$item);
    }

} // end of class
