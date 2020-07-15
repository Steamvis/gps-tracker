init: build up composer-install-prestissimo composer-install-app set-storage-link clear-cache dump-autoload copy-env migrate npm-prod queue-on

build:
	docker-compose build

up:
	docker-compose up -d

down:
	docker-compose down

composer-install-prestissimo:
	mv src/composer.json runtime/composer.json
	mv src/composer.lock runtime/composer.lock
	cp sources/composer.json src/composer.json
	echo -e "\e[1minstall composer booster\e[0m"
	docker-compose run --rm --no-deps php-fpm composer require hirak/prestissimo
	rm src/composer.lock
	mv runtime/composer.json src/composer.json
	mv runtime/composer.lock src/composer.lock
	echo -e "\e[1;37;42minstall composer booster...............................done\e[0m"

composer-install-app:
	echo -e "\e[1minstall laravel app\e[0m"
	docker-compose run --rm --no-deps php-fpm composer update --no-progress --profile --prefer-dist
	echo -e "\e[1;37;42minstall laravel app...............................done\e[0m"

set-storage-link:
	docker-compose run --rm --no-deps php-fpm chmod -R 777 storage/
	docker-compose run --rm --no-deps php-fpm php artisan storage:link

queue-on:
	echo -e "\e[1;37;42mready to use\e[0m"
	echo -e "\e[1mdocs: https://github.com/Steamvis/laravel-crm/tree/master/docs\e[0m"
	docker-compose run --rm --no-deps php-fpm php artisan queue:work

migrate:
	docker-compose run --rm --no-deps php-fpm php artisan migrate:fresh --seed

clear-cache:
	docker-compose run --rm --no-deps php-fpm php artisan cache:clear

dump-autoload:
	docker-compose run --rm --no-deps php-fpm composer dump-autoload

clear-logs:
	docker-compose run --rm --no-deps php-fpm rm -rf storage/logs/laravel.log

npm-prod:
	docker-compose run --rm --no-deps node npm install --save-dev webpack
	docker-compose run --rm --no-deps node npm run prod

copy-env:
	cp sources/env-laravel src/.env
	docker-compose run --rm --no-deps php-fpm php artisan key:generate
