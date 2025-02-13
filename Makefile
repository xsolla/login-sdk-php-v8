#!make

include environment/docker/.env

SERVICES=docker-compose -f environment/docker/docker-compose.yml
RUN=$(SERVICES) run --name $(PROJECT_PREFIX)-php --rm php

build: image install

image:
	@docker build --platform=linux/amd64 --build-arg PHP_VERSION=$(PHP_VERSION) -t $(PROJECT_PREFIX)/php-$(PHP_VERSION)-fpm ./environment/docker/php-fpm/

install:
	@$(RUN) composer install
	@$(RUN) composer --working-dir=tools/php-cs-fixer install

dump:
	@$(RUN) composer dump-autoload

push:
	@docker tag $(PROJECT_PREFIX)/php-$(PHP_VERSION)-fpm:latest registry.srv.local:5043/devops/php:$(PHP_VERSION)-fpm-$(PROJECT_PREFIX)
	@docker push registry.srv.local:5043/devops/php:$(PHP_VERSION)-fpm-$(PROJECT_PREFIX)

check-full: clear install check

check: test quality

test:
	@$(RUN) php -d zend.enable_gc=0 vendor/bin/phpunit --configuration phpunit.xml.dist

fail-test:
	@$(RUN) php -d zend.enable_gc=0 vendor/bin/phpunit --configuration phpunit.xml.dist --stop-on-failure

quality:
	@$(RUN) tools/php-cs-fixer/vendor/bin/php-cs-fixer fix
	@$(RUN) vendor/bin/phpstan analyse -c phpstan.neon

clear:
	@rm -rf ./.php_cs.cache
	@rm -rf ./vendor
	@rm -rf ./composer.lock
	@rm -rf ./tools/php-cs-fixer/vendor
	@rm -rf ./tools/php-cs-fixer/composer.lock

sh:
	@$(RUN) sh
