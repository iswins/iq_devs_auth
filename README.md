## Развертывание

- Создать файл .env из .env.example
- перейти в папку docker
- запустить команды: ```docker-compose build && docker-compose up -d```
- запустить команды: ```docker exec -it iq_devs_auth_fpm composer install --ignore-platform-reqs```
- запустить команду: ```docker exec -it iq_devs_auth_fpm php artisan key:generate```
- запустить команду: ```docker exec -it iq_devs_auth_fpm php artisan config:clear```
- запустить команду: ```docker exec -it iq_devs_auth_fpm php artisan migrate```  
- запустить команду: ```docker exec -it iq_devs_auth_fpm php artisan jwt:secret```
- запустить команды: ```docker-compose build && docker-compose up -d```  
- в результате сервис должен подняться на порте: 7081



