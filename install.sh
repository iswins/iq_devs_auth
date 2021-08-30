#!/bin/bash

cd docker && docker-compose up -d
docker exec -it iq_devs_auth_fpm composer install --ignore-platform-reqs
docker exec -it iq_devs_auth_fpm php artisan key:generate
docker exec -it iq_devs_auth_fpm php artisan config:clear
docker exec -it iq_devs_auth_fpm php artisan migrate
docker exec -it iq_devs_auth_fpm php artisan jwt:secret
docker-compose build && docker-compose down && docker-compose up -d && cd ../
