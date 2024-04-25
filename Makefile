# docker network create omuzgor_network
up:
	docker-compose up -d
composer_update:
	docker-compose run --rm php_omuzgor composer update
composer_install:
	docker-compose run --rm php_omuzgor composer install
key_gen:
	docker-compose run --rm php_omuzgor php artisan key:generate
migrate:
	docker-compose run --rm php_omuzgor php artisan migrate
seed:
	docker-compose run --rm php_omuzgor php artisan db:seed
queue:
	docker-compose run --rm php_omuzgor php artisan queue:work
down:
	docker-compose down
optimize_clear:
	docker-compose run --rm php_omuzgor php artisan optimize:clear
