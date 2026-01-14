#!/bin/sh
set -e

LOCK_FILE="/var/www/.initialized"
KEY_DIR="/var/www/keys"

echo "➡️  Entrypoint started with command: $@"

# Fix permissions & keys every startup (important with bind mounts)
fix_runtime_permissions() {
  echo "🔧 Fixing runtime permissions + Passport keys"

  # Ensure dirs exist
  mkdir -p storage bootstrap/cache "$KEY_DIR"

  # Laravel writable dirs (777 is ok for school project; otherwise 775)
  chmod -R 777 storage bootstrap/cache || true

  # Try to set owner for Laravel (may be ignored on Windows mounts; that's OK)
  chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true

  # If keys don't exist in KEY_DIR, create them there (NOT in bind-mounted storage)
  if [ ! -f "$KEY_DIR/oauth-private.key" ] || [ ! -f "$KEY_DIR/oauth-public.key" ]; then
    echo "🗝️  Generating Passport keys into $KEY_DIR"

    # generate in default location, then move (safely)
    php artisan passport:keys --force

    # Move private key only if it exists AND isn't already the same file
    if [ -f storage/oauth-private.key ]; then
      if [ ! storage/oauth-private.key -ef "$KEY_DIR/oauth-private.key" ]; then
        mv -f storage/oauth-private.key "$KEY_DIR/oauth-private.key"
      else
        echo "ℹ️ Private key already points to $KEY_DIR (same file) – skipping mv"
      fi
    fi

    # Move public key only if it exists AND isn't already the same file
    if [ -f storage/oauth-public.key ]; then
      if [ ! storage/oauth-public.key -ef "$KEY_DIR/oauth-public.key" ]; then
        mv -f storage/oauth-public.key "$KEY_DIR/oauth-public.key"
      else
        echo "ℹ️ Public key already points to $KEY_DIR (same file) – skipping mv"
      fi
    fi
  fi

  # Ensure symlinks exist in storage (Laravel expects these paths)
  ln -sf "$KEY_DIR/oauth-private.key" storage/oauth-private.key
  ln -sf "$KEY_DIR/oauth-public.key"  storage/oauth-public.key

  # Permissions/ownership for keys
  chown www-data:www-data "$KEY_DIR/oauth-private.key" "$KEY_DIR/oauth-public.key" 2>/dev/null || true
  chmod 600 "$KEY_DIR/oauth-private.key" || true
  chmod 644 "$KEY_DIR/oauth-public.key"  || true
}

# Run initialization only when command is php-fpm
if echo "$@" | grep -q "fpm"; then
  # Always fix perms/keys on every start
  fix_runtime_permissions

  if [ ! -f "$LOCK_FILE" ]; then
    echo "🟢 First container startup detected – running initialization"


if php artisan migrate:status >/dev/null 2>&1; then
  echo "ℹ️ DB already initialized – skipping"
else
  php artisan migrate --force
  if ! php artisan db:seed --force; then
      echo "⚠️ Database seeding failed, skipping"
    fi
fi

    # Passport client (only if not exists)
    if ! php artisan passport:client --list 2>/dev/null | grep -q "Client1"; then
      php artisan passport:client \
        --personal \
        --provider=users \
        --name="Client1" \
        --no-interaction
    else
      echo "ℹ️ Passport Client1 already exists"
    fi

    # Clear cached config
    php artisan config:clear || true
    php artisan cache:clear || true

    # Create lock
    touch "$LOCK_FILE"

    echo "✅ Initialization finished"
  else
    echo "ℹ️ Container already initialized – skipping setup"
  fi
else
  echo "ℹ️ Command is not php-fpm – skipping initialization"
fi

exec "$@"
