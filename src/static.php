<?php

/**
 * A set of simple functions for logging.
 *
 * @package logger
 */

namespace calguy1000\logger;

/**
 * Initialize the logging system.
 *
 * @param string $filename
 * @param int $max_age_h
 * @param int $max_size_kb
 * @param int $keepmax
 * @see Logger::init()
 */
function init($filename, $max_age_h = null, $max_size_kb = null, $keepmax = 10)
{
    SimpleLogger::init($filename,$max_age_h,$max_size_kb,$keepmax);
}

/**
 * A normal function to add a debug message to the log file.
 *
 * @param string $msg The output message.
 * @param string $section An optional section key
 * @param int $item An optional item key
 * @throws \LogicException
 */
function debug($msg,$section = null,$item = null)
{
    SimpleLogger::debug($msg,$section,$item);
}

/**
 * A normal function to add an info message to the log file.
 *
 * @param string $msg The output message.
 * @param string $section An optional section key
 * @param int $item An optional item key
 * @throws \LogicException
 */
function info($msg,$section = null,$item = null)
{
    SimpleLogger::info($msg,$section,$item);
}

/**
 * A normal function to add a warning message to the log file.
 *
 * @param string $msg The output message.
 * @param string $section An optional section key
 * @param int $item An optional item key
 * @throws \LogicException
 */
function warn($msg,$section = null,$item = null)
{
    SimpleLogger::warn($msg,$section,$item);
}

/**
 * A normal function to add an error message to the log file.
 *
 * @param string $msg The output message.
 * @param string $section An optional section key
 * @param int $item An optional item key
 * @throws \LogicException
 */
function error($msg,$section = null,$item = null)
{
    SimpleLogger::error($msg,$section,$item);
}
