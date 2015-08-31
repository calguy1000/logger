#!/usr/bin/php
<?php

require '../vendor/autoload.php';
use \calguy1000\logger as Logger;

$parms['use_archives'] = 1;
$parms['priority'] = logger\Logger::PRIORITY_INFO;
$parms['limit'] = 88;
$parms['filename'] = __DIR__.'/logs/test2.log';
$parms['msg'] = '*Evil*';
$query = new Logger\Query($parms);
$rs = $query->execute();
foreach( $rs as $key => $item ) {
    echo "KEY IS $key\n";
    print_r( $item );
}
