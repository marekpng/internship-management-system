Softverove inzinierstvo 2025 
Internship management system develop

commands:

 php artisan serve
 php artisan migrate:fresh 
 php artisan passport:install
 php artisan db:seed --class=RoleSeeder

docker mailpit:
docker run -d --name mailpit -p 1025:1025 -p 8025:8025 axllent/mailpit

//vytvorenie zaznamu do oauth_clients tabulky na tokeny vytvaranie
 php artisan passport:client --personal --provider=users


// DOCKERIZACIA
- pri prvom spusteni je potrebne  pouzit build

docker-compose build
docker-compose up -d


priklad .env ako to mam setupnute:

APP_NAME=Laravel
APP_ENV=local
APP_KEY=asdfasdfasdf
APP_DEBUG=true
APP_TIMEZONE=UTC
APP_URL=http://localhost

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

APP_MAINTENANCE_DRIVER=file
APP_MAINTENANCE_STORE=database

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=internship-management-system
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database

CACHE_STORE=database
CACHE_PREFIX=

MEMCACHED_HOST=127.0.0.1

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=127.0.0.1
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="mjaros@student.ukf.com"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

VITE_APP_NAME="${APP_NAME}"

#  Client ID ................................................................................................... asdf123
#  Client Secret ........................................................................................... asdf123123
PASSPORT_PERSONAL_ACCESS_CLIENT_ID=asdf123123
PASSPORT_PERSONAL_ACCESS_CLIENT_SECRET=asdf123123


INTEGRACIA EXTERNEHO SYSTEMU:

EXTERNAL system - prikaz pre vygenerovanie tokenu:
php artisan external:token



