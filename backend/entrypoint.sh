#!/bin/sh
set -e

LOCK_FILE="/var/www/.initialized"

echo "➡️  Entrypoint started with command: $@"

# Spúšťaj inicializáciu iba ak je command php-fpm
if echo "$@" | grep -q "fpm"; then
    if [ ! -f "$LOCK_FILE" ]; then
        echo "🟢 First container startup detected – running initialization"

        # Permissions (dočasne 777)
        chmod -R 777 storage bootstrap/cache

        # Laravel migrate + seed
        php artisan migrate --force
        if ! php artisan db:seed --force; then
            echo "⚠️ Database seeding failed, skipping"
        fi


        # Passport client (iba ak ešte neexistuje)
        if ! php artisan passport:client --list | grep -q "Client1"; then
            php artisan passport:client \
                --personal \
                --provider=users \
                --name="Client1" \
                --no-interaction
        else
            echo "ℹ️ Passport Client1 already exists"
        fi

        # OAuth key permissions
        chmod 600 storage/oauth-private.key storage/oauth-public.key

        # Vytvor lock
        touch "$LOCK_FILE"

        echo "✅ Initialization finished"
    else
        echo "ℹ️ Container already initialized – skipping setup"
    fi
else
    echo "ℹ️ Command is not php-fpm – skipping initialization"
fi

# Spusti pôvodný CMD
exec "$@"
