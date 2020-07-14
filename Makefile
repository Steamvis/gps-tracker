init: build up set-storage-link clear-cache dump-autoload migrate npm-prod queue-on

build:
	docker-compose build

up:
	docker-compose up -d

down:
	docker-compose down

enter-mysql:
	docker-compose exec mysql mysql -uroot -proot

set-storage-link:
	docker-compose run --rm --no-deps php-fpm chmod -R 777 storage/
	rm src/public/storage
	docker-compose exec php-fpm php artisan storage:link

queue-on:
	docker-compose exec php-fpm php artisan queue:work

migrate:
	sleep 5
	docker-compose exec php-fpm php artisan migrate:fresh --seed

clear-cache:
	docker-compose run --rm --no-deps php-fpm php artisan cache:clear

dump-autoload:
	docker-compose run --rm --no-deps php-fpm composer dump-autoload

clear-logs:
	docker-compose run --rm --no-deps php-fpm rm -rf storage/logs/laravel.log

npm-prod:
	docker-compose run --rm --no-deps node npm run prod

