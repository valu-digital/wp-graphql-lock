#!/bin/sh

set -eu

if [ "${WP_DOCKER:-}" != "1" ]; then
    >&2 echo "This script is for the Docker container init."
    exit 1
fi

composer install

if [ -f init-docker.sh ]; then
    exec ./init-docker.sh
else
    >&2 echo "Init ok! Start the shell in antoher terminal with docker/shell.sh"
    exec tail -f /dev/null
fi
