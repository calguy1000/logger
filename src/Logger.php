<?php

/**
 * The primary logging class to the logger library.
 *
 * @package logger
 */

namespace calguy1000\logger;

/**
 * The primary logging class for the logger library.
 *
 * @package   Logger
 * @author    Robert Campbell <calguy1000@gmail.com>
 * @copyright 2015
 * @license   LGPL2.1
 */
interface Logger
{
    /**
     * debug priority
     *
     * @var string
     */
    const PRIORITY_DEBUG = 'debug';

    /**
     * info priority
     *
     * @var string
     */
    const PRIORITY_INFO = 'info';

    /**
     * warning priority
     *
     * @var string
     */
    const PRIORITY_WARN = 'warn';

    /**
     * error priority
     *
     * @var string
     */
    const PRIORITY_ERROR = 'error';

    /**
     * A convenience function to add a debug message to the log file.
     *
     * @param  string $msg     The output message.
     * @param  string $section An optional section key
     * @param  int    $item    An optional item key
     * @throws \InvalidArgumentException
     */
    public function debug();

    /**
     * A convenience function to add an info message to the log file.
     *
     * @param  string $msg     The output message.
     * @param  string $section An optional section key
     * @param  int    $item    An optional item key
     * @throws \InvalidArgumentException
     */
    public function info();

    /**
     * A convenience function to add a warning message to the log file.
     *
     * @param  string $msg     The output message.
     * @param  string $section An optional section key
     * @param  int    $item    An optional item key
     * @throws \InvalidArgumentException
     */
    public function warn();

    /**
     * A convenience function to add an error message to the log file.
     *
     * @param  string $msg     The output message.
     * @param  string $section An optional section key
     * @param  int    $item    An optional item key
     * @throws \InvalidArgumentException
     */
    public function error();
} // end of class
