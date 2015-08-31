#!/usr/bin/php
<?php

require '../vendor/autoload.php';

// simple test using the normal logger.
@mkdir(__DIR__.'/logs');
$logger = new \calguy1000\logger\Logger(__DIR__.'/logs/logger_test1.log',6,50);
$logger->info('foo');
$logger->warn('something','Test',1);
$logger->warn('something','Test',1); // a repeated entry
$logger->warn('something','Test',1); // a repeated entry
$logger->debug('some debug message','Test',1);
$logger->warn('something else','Test',1);
$logger->warn('something else','Test',1); // another repeat entry
$logger->error('some error');
