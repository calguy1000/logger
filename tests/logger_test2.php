#!/usr/bin/php
<?php

require '../vendor/autoload.php';
use \calguy1000\logger\Logger as Logger;

// test two loggers writing to the same file with different rotation settings.
@mkdir(__DIR__.'/logs');

// create a couple lf loggers, both pointing at the same file...
$loggers[] = new Logger(__DIR__.'/logs/logger_test2.log',6,50);
$loggers[] = new Logger(__DIR__.'/logs/logger_test2.log',5,50);

// write some log entries
for( $item = 0; $item < 1000; $item++ ) {
    $the_logger = $loggers[$item % 2];
    $section = 'Logger'.($item % 2);
    $msg = "Test message from Logger";

    // allways use info for this test, though it really doesn't matter.
    $the_logger->info($msg,$section,$item);
}
