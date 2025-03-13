# Currency Rate Fetcher and Converter

## Description
This is a Laravel-based application that fetches currency exchange rates from a public REST API, stores the rates in a MySQL database, and provides a console command to convert amounts between two specified currencies.

## Features
- Fetches the latest currency exchange rates from an external API.
- Stores the exchange rates in a MySQL database.
- Provides a console command to convert amounts between different currencies.
- Error handling and logging for better monitoring.
- Caching implemented to reduce API calls.

## Requirements
- PHP 8.x
- Composer
- Docker & Docker Compose
- MySQL 8.x
- Redis

## Getting Started

### 1. Install Docker Desktop
### 2. Clone the repository
git clone https://github.com/ankurpareek/currency-rate-fetcher.git
cd currency-rate-fetcher
### 3. Create a environment file with below secrets
APP_ENV=local
APP_KEY=base64:1Sx5Ai6HwrOkD7P8vlHArjNgWR4sFQD8OmlS8DJ4fS0=
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=exchange-mysql
DB_PORT=3306
DB_DATABASE=exchange
DB_USERNAME=root
DB_PASSWORD=root

REDIS_CLIENT=phpredis
REDIS_HOST=exchange-redis
REDIS_PASSWORD=null
REDIS_PORT=6379

EXCHANGE_API_DOMAIN=https://api.exchangerate.host
EXCHANGE_API_KEY=4cda3ac80119458cee4346b755bef50c

### 4. Add the secret file path in docker-compose.yml in {env_file}
### 5. Run below command to build and launch container
docker compose up
### 6. Login Into the container using below command
docker exec -it exchange-app /bin/bash
### 7. Execute the below command to run migration
php artisan migrate
### 8. Now application is ready to convert currrency, run below command
php artisan currency:convert 1 USD INR
### 9. To Run Test Cases, execute below command
php artisan test