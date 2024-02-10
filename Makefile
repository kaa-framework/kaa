build:
	@docker build etc/php -t kaa/php

hook:
	@cp -r etc/hook/* .git/hooks

php:
	@docker run --rm -it -v $(shell pwd):/app kaa/php bash

demo:
	@docker run --rm -it -v $(shell pwd):/app -v $(shell pwd)/../demo:/demo -w /demo -p 8800:8800 --env PHP_IDE_CONFIG='serverName=kaa-demo' kaa/php bash

kphp:
	@docker run --rm -it -v $(shell pwd):/app -v $(shell pwd)/../demo:/demo -w /demo -p 8801:8801 vkcom/kphp bash

demo2:
	@docker run --rm -it -v $(shell pwd):/app -v $(shell pwd)/../demo:/demo -w /demo kaa/php bash

chown:
	@sh etc/script/chown.sh

stan:
	@docker run --rm -t -v $(shell pwd):/app kaa/php vendor/bin/phpstan --configuration=fixer/phpstan.neon

lint:
	@docker run --rm -t -v $(shell pwd):/app kaa/php vendor/bin/ecs check --config=fixer/ecs.php

lintfix:
	@sh etc/script/ecs.sh

pest:
	@docker run --rm -t -v $(shell pwd):/app kaa/php php vendor/bin/pest -c fixer/phpunit.xml.dist --compact --display-errors --display-incomplete --display-skipped --display-notices --display-warnings || EXIT=1
