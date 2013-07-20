#!/bin/sh
if [ "X$1" = "Xstart" ] ; then
    chmod +x phpsocketdaemon.php
    php phpsocketdaemon.php >> chat.log &
    echo "Starting chat"
fi