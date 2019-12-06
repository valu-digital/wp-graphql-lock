#!/bin/sh

set -eu

eval $(grep -v '^#' .env)

command=$@

if [ "$command" = "" ]; then
    command="bash -l"
    >&2 echo
    >&2 echo "Run the tests with: codecept run wpunit"
    >&2 echo
fi

exec docker exec -it "${CONTAINER_NAME}-wp" $command