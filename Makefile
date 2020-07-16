# VARIABLES
#
# FOLDERS

RUNTIME = ./runtime
SRC = ./src
SOURCES = ./sources
NODE_MODULES = $(SRC)/node_modules

# FILES

COMPOSER.JSON = composer.json
COMPOSER.LOCK = composer.lock


init: build up composer-install-prestissimo composer-install-app set-storage-link clear-cache dump-autoload copy-env migrate npm-install npm-prod

build:
	docker-compose build

up:
	docker-compose up -d

down:
	docker-compose down

queue-on: clear-cache
	docker exec -it src-php-fpm php artisan queue:work

composer-install-prestissimo:
	if [ -d $(RUNTIME) ]; then rm -rf $(RUNTIME); fi
	mkdir runtime
	if [ -f $(SRC)/$(COMPOSER.JSON) ]; then mv $(SRC)/$(COMPOSER.JSON) $(RUNTIME)/$(COMPOSER.JSON); fi
	if [ -f $(SRC)/$(COMPOSER.LOCK) ]; then mv $(SRC)/$(COMPOSER.LOCK) $(RUNTIME)/$(COMPOSER.LOCK); fi
	cp $(SOURCES)/$(COMPOSER.JSON) $(SRC)/$(COMPOSER.JSON)
	echo -e "\e[1minstall composer booster\e[0m"
	docker-compose run --rm --no-deps php-fpm composer require hirak/prestissimo
	if [ -f "./src/composer.lock" ]; then rm -f src/composer.lock; fi
	if [ -f $(RUNTIME)/$(COMPOSER.JSON) ]; then mv $(RUNTIME)/$(COMPOSER.JSON) $(SRC)/$(COMPOSER.JSON); fi
	if [ -f $(RUNTIME)/$(COMPOSER.LOCK) ]; then mv $(RUNTIME)/$(COMPOSER.LOCK) $(SRC)/$(COMPOSER.LOCK); fi
	echo -e "\e[1;37;42minstall composer booster...............................done\e[0m"

composer-install-app:
	echo -e "\e[1minstall laravel app\e[0m"
	docker-compose run --rm --no-deps php-fpm composer install --no-progress --profile --prefer-dist
	echo -e "\e[1;37;42minstall laravel app...............................done\e[0m"

set-storage-link:
	docker-compose run --rm --no-deps php-fpm chmod -R 777 storage/
	docker-compose run --rm --no-deps php-fpm php artisan storage:link

migrate:
	docker-compose run --rm --no-deps php-fpm php artisan migrate:fresh --seed

npm-install:
	if [ -d $(NODE_MODULES) ]; then docker-compose run --rm --no-deps node rm -rf $(NODE_MODULES); fi
	docker-compose run --rm --no-deps node npm i

npm-prod:
	docker-compose run --rm --no-deps node npm run prod
	echo -e "\e[1;37;42mready to use\e[0m"
	#
	# site: http://localhost:8080/
	#
	#
	# docs: https://github.com/Steamvis/laravel-crm/tree/master/docs
	#
	#
	# enable queue write
	# make queue-on

copy-env:
	if [ -f $(SRC)/.env ]; then mv $(SRC)/.env $(RUNTIME)/.env; fi
	cp $(SOURCES)/env-laravel $(SRC)/.env
	docker-compose run --rm --no-deps php-fpm php artisan key:generate

clear-logs:
	docker-compose run --rm --no-deps php-fpm rm -rf storage/logs/laravel.log

clear-cache:
	docker-compose run --rm --no-deps php-fpm php artisan cache:clear

dump-autoload:
	docker-compose run --rm --no-deps php-fpm composer dump-autoload
