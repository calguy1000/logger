<?php

namespace calguy1000\logger;

function init($filename, $max_age = null, $max_size = null, $keepmax = 10)
{
    SimpleLogger::init($filename,$max_age,$max_size,$keepmax);
}

function debug($msg,$section = null,$item = null)
{
    SimpleLogger::debug($msg,$section,$item);
}

function info($msg,$section = null,$item = null)
{
    SimpleLogger::info($msg,$section,$item);
}

function warn($msg,$section = null,$item = null)
{
    SimpleLogger::warn($msg,$section,$item);
}

function error($msg,$section = null,$item = null)
{
    SimpleLogger::error($msg,$section,$item);
}
