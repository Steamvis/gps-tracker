init: build up composer-install-prestissimo composer-install-app set-storage-link clear-cache dump-autoload copy-env migrate npm-install npm-prod

build:
	docker-compose build

up:
	docker-compose up -d

down:
	docker-compose down

composer-install-prestissimo:
	if [ -d "./runtime" ]; then rm -rf runtime; fi
	mkdir runtime
	if [ -f "./src/composer.json" ]; then mv ./src/composer.json ./runtime/composer.json; fi
	if [ -f "./src/composer.lock" ]; then mv ./src/composer.lock ./runtime/composer.lock; fi
	cp sources/composer.json src/composer.json
	echo -e "\e[1minstall composer booster\e[0m"
	docker-compose run --rm --no-deps php-fpm composer require hirak/prestissimo
	if [ -f "./src/composer.lock" ]; then rm -f src/composer.lock; fi
	if [ -f "./runtime/composer.json" ]; then mv ./runtime/composer.json ./src/composer.json; fi
	if [ -f "./runtime/composer.lock" ]; then mv ./runtime/composer.lock ./src/composer.lock; fi
	echo -e "\e[1;37;42minstall composer booster...............................done\e[0m"

composer-install-app:
	echo -e "\e[1minstall laravel app\e[0m"
	docker-compose run --rm --no-deps php-fpm composer update --no-progress --profile --prefer-dist
	echo -e "\e[1;37;42minstall laravel app...............................done\e[0m"

set-storage-link:
	docker-compose run --rm --no-deps php-fpm chmod -R 777 storage/
	docker-compose run --rm --no-deps php-fpm php artisan storage:link

queue-on:
	docker-compose run --rm --no-deps php-fpm php artisan queue:work

migrate:
	docker-compose run --rm --no-deps php-fpm php artisan migrate:fresh --seed

clear-cache:
	docker-compose run --rm --no-deps php-fpm php artisan cache:clear

dump-autoload:
	docker-compose run --rm --no-deps php-fpm composer dump-autoload

clear-logs:
	docker-compose run --rm --no-deps php-fpm rm -rf storage/logs/laravel.log

npm-install:
	if [ -d "./src/node_modules" ]; then docker-compose run --rm --no-deps node rm -rf ./src/node_modules; fi
	docker-compose run --rm --no-deps node npm i

npm-prod:
	mv ./src/webpack.mix.js ./runtime/webpack.mix.js
	cp ./sources/webpack.mix.js ./src/webpack.mix.js
	docker-compose run --rm --no-deps node npm run prod
	rm -f ./src/webpack.mix.js
	mv ./runtime/webpack.mix.js ./src/webpack.mix.js
	docker-compose run --rm --no-deps node npm run prod
	if [ -d "./runtime" ]; then rm -rf runtime; fi
	echo -e "\e[1;37;42mready to use\e[0m"
	echo -e "\e[1mdocs: https://github.com/Steamvis/laravel-crm/tree/master/docs\e[0m"

copy-env:
	cp sources/env-laravel src/.env
	docker-compose run --rm --no-deps php-fpm php artisan key:generate
