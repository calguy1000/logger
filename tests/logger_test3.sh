#!/bin/sh

php ./logger_test3.php app1 &
php ./logger_test3.php app2 &
wait
echo "DONE";
