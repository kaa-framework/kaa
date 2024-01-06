#!/bin/sh

set -e
set +x

if [ -z $KAA_NO_TTY ]; then
  CMD="docker run --rm -t --user=${UID} -v $(pwd):/app kaa/php vendor/bin/ecs check --config=fixer/ecs.php --fix"
else
  CMD="docker run --rm --user=${UID} -v $(pwd):/app kaa/php vendor/bin/ecs check --config=fixer/ecs.php --fix"
fi

eval "${CMD}"
