#!/bin/sh
# process PHP script approximately every 10 seconds (including allowance for CPU delay)
# set paths for environment!
#
# note, additonal lines are for options if you want to script that way 
#

/usr/local/bin/php -f  /home/youraccount/mailqueue/mailqueue.php $1

#sleep 15
#/usr/local/bin/php -f  /home/youraccount/mailqueue/mailqueue.php $1

#sleep 15
#/usr/local/bin/php -f  /home/youraccount/mailqueue/mailqueue.php
#sleep 15
