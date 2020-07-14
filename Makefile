init: build migrate storage-link-clean storage-chmod storage-link-set

build:
	docker-compose build

up:
	docker-compose up -d

down:
	docker-compose down

storage-chmod:
	sudo chmod 775 -R src/storage/

storage-link-clean:
	rm src/public/storage

storage-link-set:
	docker-compose exec php-fpm php artisan storage:link

migrate:
	docker-compose exec php-fpm php artisan migrate:fresh --seed

