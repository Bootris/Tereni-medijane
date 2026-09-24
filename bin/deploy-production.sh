#!/usr/bin/env bash
# Rebuild the app in place on the production server, after new code has been
# rsynced in. Invoked over SSH by .github/workflows/deploy.yml; can also be run
# by hand as root:  bash <app>/bin/deploy-production.sh
set -euo pipefail

APP="$(cd "$(dirname "$0")/.." && pwd)"
cd "$APP"
export COMPOSER_ALLOW_SUPERUSER=1 NODE_OPTIONS=--max-old-space-size=1536

# Preflight: .env i SQLite baza zive samo na serveru (rsync ih ne salje).
# Bez njih bi deploy pukao na nejasnom mestu, pa se proveravaju odmah.
if [ ! -f "$APP/.env" ]; then
    echo "GRESKA: nema $APP/.env - napravi ga na serveru pre prvog deploya." >&2
    exit 1
fi
if grep -qE '^DB_CONNECTION=sqlite' "$APP/.env" && [ ! -f "$APP/database/database.sqlite" ]; then
    echo "GRESKA: nema $APP/database/database.sqlite - napravi bazu na serveru:" >&2
    echo "  touch $APP/database/database.sqlite && php artisan migrate --force --seed" >&2
    exit 1
fi

php artisan down --render="errors::503" --retry=15 || true
trap 'cd "$APP" && php artisan up || true' EXIT

composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-progress
npm ci --no-audit --no-fund
npm run build
php artisan migrate --force --no-interaction

chown -R www-data:www-data "$APP"
chmod -R 775 "$APP/storage" "$APP/bootstrap/cache"
chmod 640 "$APP/.env"
# SQLite baza je van gita (rsync je ne dira); prava se popravljaju samo ako postoji.
if [ -f "$APP/database/database.sqlite" ]; then
    chmod 775 "$APP/database"
    chmod 664 "$APP/database/database.sqlite"
fi

sudo -u www-data php artisan optimize
rm -rf "$APP/node_modules"          # build artifacts already in public/build

# Ime php-fpm servisa zavisi od PHP verzije na serveru - nadji ga umesto da ga pogadjas.
FPM="$(systemctl list-units --no-legend --type=service 'php*-fpm.service' | awk '{print $1}' | head -1)"
systemctl restart "${FPM:?php-fpm servis nije pronadjen}"

echo "✅ $APP redeployovan (${FPM})."
