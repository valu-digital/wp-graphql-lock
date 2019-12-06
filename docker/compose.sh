#!/bin/sh

set -eu

echo "# Generated file. Do not edit. Edit .env.docker instead" > .env
echo "" >> .env
cat .env.docker >> .env

command=$@

if [ "$command" = "" ]; then
    command="up --abort-on-container-exit --build"
fi

exec docker-compose -f docker/docker-compose.yml $command