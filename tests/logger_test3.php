#!/usr/bin/php
<?php

require '../vendor/autoload.php';
use \calguy1000\logger\AutoRotateFileLogger as Logger;

// a simple way to test multiple apps writing to the same log file at the same time.
// call this function like php logger_test3.php sectionName
@mkdir(__DIR__.'/logs');

if( !isset($argv[1]) ) die("No sectionName parameter specified");
$sectionName = $argv[1];

// create a couple lf loggers, both pointing at the same file...
$logger = new Logger(__DIR__.'/logs/logger_test3.log',6,50);

// write some log entries
for( $item = 0; $item < 500; $item++ ) {
    $msg = "Test message from Logger";

    // go to sleep a bit.
    $v = rand(300,400);
    usleep($v);

    // allways use info for this test, though it really doesn't matter.
    $logger->info($msg,$sectionName,$item);
}
