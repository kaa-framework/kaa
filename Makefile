build:
	@docker build etc/php -t kaa/php

hook:
	@cp -r etc/hook/* .git/hooks

php:
	@docker run --rm -it -v $(shell pwd):/app kaa/php bash

chown:
	@sh etc/script/chown.sh

stan:
	@docker run --rm -t -v $(shell pwd):/app kaa/php vendor/bin/phpstan --configuration=fixer/phpstan.neon

lint:
	@docker run --rm -t -v $(shell pwd):/app kaa/php vendor/bin/ecs check --config=fixer/ecs.php

lintfix:
	@sh etc/script/ecs.sh

unit:
	@docker run --rm -t -v $(shell pwd):/app kaa/php vendor/bin/phpunit -c fixer/phpunit.xml.dist
