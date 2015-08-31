#!/usr/bin/php
<?php

require '../vendor/autoload.php';
// this tests log rotation, and repeated strings, string truncation, newlines in the input
// but lines are somewhat similar.

@mkdir('logs',0777,TRUE);
\calguy1000\logger\init(__DIR__.'/logs/simple_test2.log',6,50);
$str = <<<EOT
Contrary to popular belief, Lorem Ipsum is not simply random text. It has roots in a piece of classical Latin literature from 45 BC,
making it over 2000 years old. Richard McClintock, a Latin professor at Hampden-Sydney College in Virginia, looked up one of the more
obscure Latin words, consectetur, from a Lorem Ipsum passage, and going through the cites of the word in classical literature, discovered
the undoubtable source. Lorem Ipsum comes from sections 1.10.32 and 1.10.33 of "de Finibus Bonorum et Malorum"
(The Extremes of Good and Evil) by Cicero, written in 45 BC. This book is a treatise on the theory of ethics, very popular during
the Renaissance. The first line of Lorem Ipsum, "Lorem ipsum dolor sit amet..", comes from a line in section 1.10.32.
EOT;
$list = array('calguy1000\logger\debug','calguy1000\logger\info','calguy1000\logger\warn',
              'calguy1000\logger\error');
for( $n = 0; $n < 500; $n++ ) {
    $len = rand(20,strlen($str));
    $off = rand(0,strlen($str)-$len);
    $sev = rand(0,3);
    $func = $list[$sev];
    for( $i = 0; $i < rand(1,50); $i++ ) {
        $func(substr($str,$off,$len),'Test',2);
    }
}
