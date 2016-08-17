#!/usr/bin/php
<?php

require '../vendor/autoload.php';

// simple test using the normal logger.
@mkdir(__DIR__.'/logs');
$logger = new \calguy1000\logger\AutoRotateFileLogger(__DIR__.'/logs/logger_test1.log',6,50);
$logger->info('info 1');
$logger->info('info 2');
$logger->warn('something','Test',1);
$logger->warn('something','Test',1); // a repeated entry
$logger->warn('something','Test',1); // a repeated entry
$logger->debug('some debug message','Test',1);
$logger->debug('something else','Test',1);
$logger->debug('something else','Test',1); // another repeat entry
$logger->info('info 2');
$logger->error('some error');
