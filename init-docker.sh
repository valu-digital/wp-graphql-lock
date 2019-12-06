#!/bin/sh

set -eu


composer wp-install

>&2 echo "Init ok! Start the shell in antoher terminal with docker/shell.sh"

cd .wp-install/web
set -x
exec wp server --host=0.0.0.0