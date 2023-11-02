build:
	@docker build etc/php -t kaa/php

hook:
	@cp -r etc/hook/* .git/hooks

php:
	@docker run -it -v $(shell pwd):/app kaa/php bash

chown:
	@sh etc/script/chown.sh

stan:
	@docker run -t -v $(shell pwd):/app kaa/php vendor/bin/phpstan --configuration=fixer/phpstan.neon

lint:
	@docker run -t -v $(shell pwd):/app kaa/php vendor/bin/ecs check --config=fixer/ecs.php

lintfix:
	@sh etc/script/ecs.sh

unit:
	@docker run -t -v $(shell pwd):/app kaa/php vendor/bin/phpunit -c fixer/phpunit.xml.dist

md:
	@docker run -t -v $(shell pwd):/app kaa/php vendor/bin/phpmd src ansi fixer/phpmd.xml
