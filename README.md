# aspire-app
It is set of API's for managing loan applications for registered users by bank managers.

Framework - Laravel (https://laravel.com/docs/5.7)
1) Setup local dev environment taking reference of above link (PHP >= 7.1.3)
2) Copy .env.example to .env file & use your configuration (create database - aspire_app & provide required details -hostname, user, password, port)
3) Execute "composer update" in terminal to download project specific dependencies (vendor folder, Reach to your poject folder for executing this cmd)
4) Execute "php artisan migrate:fresh" in terminal to setup tables (Reach to your poject folder for executing this cmd)
5) Execute "php artisan db:seed" in terminal to pre-configure tables with relevant dataset (Reach to your poject folder for executing this cmd)
6) Execute "php artisan passport:install" & "passport:generate" in terminal to setup passport package and keys (Reach to your poject folder for executing this cmd)
7) Hit your API endpoints for accessing required details (For more details, refer to sent email)
