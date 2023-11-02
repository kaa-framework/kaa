#!/bin/sh

set -e
set +x

if [ -z $KAA_NO_TTY ]; then
  CMD="docker run -t --user=${UID} -v $(pwd):/app kaa/php vendor/bin/ecs check --config=fixer/ecs.php --fix"
else
  CMD="docker run --user=${UID} -v $(pwd):/app kaa/php vendor/bin/ecs check --config=fixer/ecs.php --fix"
fi

eval "${CMD}"
