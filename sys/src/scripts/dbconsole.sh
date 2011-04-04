#!/bin/sh

cd `dirname $0`
cmd=`./_dbconsole_command.php`
exec $cmd
