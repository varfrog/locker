.PHONY: start stop init tests enter generate-encryption-key

start:
	docker-compose up -d

stop:
	docker-compose stop

init:
	docker-compose build
	docker-compose up -d
	docker-compose exec php composer install
	docker-compose exec php php bin/console doctrine:database:create --if-not-exists
	docker-compose exec php php bin/console doctrine:migrations:migrate --no-interaction
	docker-compose exec php php bin/console doctrine:fixtures:load --no-interaction
	docker-compose exec php php bin/console app:dump-encryption-key

tests:
	docker-compose exec php php bin/console --env=test doctrine:database:create
	docker-compose exec php php bin/console --env=test doctrine:migrations:migrate --no-interaction
	docker-compose exec php php vendor/bin/simple-phpunit

enter:
	docker exec -it securestorage_php bash
