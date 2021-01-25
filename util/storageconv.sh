#!/bin/sh

WWWUSER=$(ps aux | egrep '([a|A]pache|[h|H]ttpd|lighttpd|[n|N]ginx|h2o)' | awk '{ print $1}' | uniq | grep -v `whoami` | tail -1)

sudo -u $WWWUSER php util/storageconv $*

