# TABLE OF CONTENTS
#
# MAKEFILE VARIABLES
#	FOLDERS
#	FILES
# DOCKER
#	INSTALLATION
#	CONTROLLING
#		BACKEND
#		FRONTEND
#	NODE JS
#	DATABASE
#	LOGS/CACHE
#	QUEUE


################################################################
###################### MAKE VARIABLES ##########################
################################################################
######################### FOLDERS ##############################
################################################################

SRC = ./src
ENVIRONMENT = ./environment
RUNTIME = $(ENVIRONMENT)/runtime

BACKEND = $(SRC)/backend
FRONTEND = $(SRC)/frontend

BACKEND_NODE_MODULES = $(FRONTEND)/node_modules

################################################################
########################### FILES ##############################
################################################################

COMPOSER.JSON = composer.json
COMPOSER.LOCK = composer.lock

################################################################
########################## DOCKER ##############################
################################################################
####################### INSTALLATION ###########################
################################################################

init: build up composer-install-app set-storage-link clear-cache dump-autoload copy-env migrate npm-install npm-prod success-install

build:
	docker-compose build

composer-install-app:
	echo -e "\e[1minstall laravel app\e[0m"
	docker-compose run --rm --no-deps php-cli composer install --no-progress --profile --prefer-dist
	echo -e "\e[1;37;42minstall laravel app...............................done\e[0m"

set-storage-link:
	docker-compose run --rm --no-deps php-cli chmod -R 777 storage/
	docker-compose run --rm --no-deps php-cli php artisan storage:link

copy-env:
	if [ -f $(BACKEND)/.env ]; then mv $(BACKEND)/.env $(RUNTIME)/.env; fi
	cp $(ENVIRONMENT)/env-laravel $(BACKEND)/.env
	docker-compose run --rm --no-deps php-cli php artisan key:generate

success-install:
	clear
	echo -e "\e[1;37;42mready to use\e[0m"
	#
	# add to hosts file
	#
	# 127.0.0.1 api.lcrm.test
 	# 127.0.0.1 lcrm.test
	#
	# site: http://lcrm.test/
	# api:	http://api.lcrm.test/
	#
	#
	# docs: https://github.com/Steamvis/laravel-crm/tree/master/docs
	#
	#
	# enable queue write
	# make queue-on

################################################################
################### DOCKER CONTROLLING #########################
################################################################

up:
	docker-compose up -d

down:
	docker-compose down

################################################################
################### CONTROLLING BACKEND ########################
################################################################

php:
	docker-compose run --rm --no-deps php-cli

php-bash:
	docker-compose run --rm --no-deps php-cli bash

################################################################
################## CONTROLLING FRONTEND ########################
################################################################

node:
	docker-compose exec node bash

################################################################
######################### NODE JS ##############################
################################################################

npm-install:
	if [ -d $(BACKEND_NODE_MODULES) ]; then docker-compose run --rm --no-deps node rm -rf $(BACKEND_NODE_MODULES); fi
	docker-compose run --rm --no-deps node npm i

npm-prod:
	docker-compose run --rm --no-deps node npm run prod
	#docker-compose run --rm --no-deps node npm run prod

################################################################
######################## DATABASE ##############################
################################################################

migrate:
	docker-compose run --rm --no-deps php-cli php artisan migrate:fresh --seed

################################################################
####################### LOGS/CACHE #############################
################################################################

logs-docker:
	docker-compose logs

clear-logs:
	docker-compose run --rm --no-deps php-cli rm -rf storage/logs/laravel.log

clear-cache:
	docker-compose run --rm --no-deps php-cli php artisan cache:clear

dump-autoload:
	docker-compose run --rm --no-deps php-cli composer dump-autoload

################################################################
########################## QUEUE ###############################
################################################################

queue-on: clear-cache
	docker exec -it src-php-fpm php artisan queue:work

################################################################
########################## OTHER ###############################
################################################################

chown:
	sudo chown -R $(USER) src/

routes:
	docker-compose run --rm --no-deps php-cli php artisan route:list
