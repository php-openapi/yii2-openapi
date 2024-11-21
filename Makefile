PHPARGS=-dmemory_limit=64M
#PHPARGS=-dmemory_limit=64M -dzend_extension=xdebug.so -dxdebug.remote_enable=1 -dxdebug.remote_host=127.0.0.1 -dxdebug.remote_autostart=1
#PHPARGS=-dmemory_limit=64M -dxdebug.remote_enable=1

UID=$(shell id -u)

all:

check-style:
	vendor/bin/php-cs-fixer fix --diff --dry-run

check-style-from-host:
	docker-compose run --rm php sh -c 'vendor/bin/php-cs-fixer fix --diff --dry-run'

fix-style:
	vendor/bin/indent --tabs composer.json
	vendor/bin/indent --spaces .php_cs.dist
	vendor/bin/php-cs-fixer fix src/ --diff

install:
	composer install --prefer-dist --no-interaction

test:
	php $(PHPARGS) vendor/bin/phpunit

clean_all:
	docker-compose down
	sudo rm -rf tests/tmp/*

clean:
	sudo rm -rf tests/tmp/app/*
	sudo rm -rf tests/tmp/docker_app/*

down:
	docker-compose down --remove-orphans

up:
	docker-compose up -d
	echo "Waiting for mariadb to start up..."
	docker-compose exec -T mysql timeout 60s sh -c "while ! (mysql -udbuser -pdbpass -h maria --execute 'SELECT 1;' > /dev/null 2>&1); do echo -n '.'; sleep 0.1 ; done; echo 'ok'" || (docker-compose ps; docker-compose logs; exit 1)

	echo "Waiting for Mysql to start up..."
	docker-compose exec -T mysql timeout 60s sh -c "while ! (mysql -udbuser -pdbpass -h mysql --execute 'SELECT 1;' > /dev/null 2>&1); do echo -n '.'; sleep 0.1 ; done; echo 'ok'" || (docker-compose ps; docker-compose logs; exit 1)

cli:
	docker-compose exec --user=$(UID) php bash

cli_root:
	docker-compose exec --user="root" php bash

cli_mysql:
	docker-compose exec --user=$(UID) mysql bash

migrate:
	docker-compose run --user=$(UID) --rm php sh -c 'mkdir -p "tests/tmp/app"'
	docker-compose run --user=$(UID) --rm php sh -c 'mkdir -p "tests/tmp/docker_app"'
	docker-compose run --user=$(UID) --rm php sh -c 'cd /app/tests && ./yii migrate  --interactive=0'

installdocker:
	docker-compose run --user=$(UID) --rm php composer install && chmod +x tests/yii

tests_dir_write_permission:
	docker-compose run --user="root" --rm php chmod -R 777 tests/tmp/ # TODO avoid 777 https://github.com/cebe/yii2-openapi/issues/156

testdocker:
	docker-compose run --user=$(UID) --rm php sh -c 'vendor/bin/phpunit --repeat 3'

efs: clean_all up migrate # Everything From Scratch

.PHONY: all check-style fix-style install test clean clean_all up down cli installdocker migrate testdocker efs


# Docs:

# outside docker
#     clean_all
#     clean (in both)
#     up
#     cli
#     migrate
#     installdocker
#     testdocker

# inside docker
#     check-style
#     fix-style
#     install
#     test
#     clean (in both)
