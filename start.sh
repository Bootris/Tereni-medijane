#!/usr/bin/env bash
#
# Pokretanje sajta za lokalni razvoj.
#
#   ./start.sh            → pokreće server na http://127.0.0.1:8000
#   PORT=8080 ./start.sh  → pokreće na drugom portu
#
set -e
cd "$(dirname "$0")"

PORT="${PORT:-8000}"

# .env mora da postoji (sa DB kredencijalima)
if [ ! -f .env ]; then
    cp .env.example .env
    php artisan key:generate
    echo "⚠  Kreiran je .env iz .env.example - popuni DB_* kredencijale pa pokreni ponovo."
    exit 1
fi

# Composer zavisnosti
if [ ! -d vendor ]; then
    echo "→ composer install..."
    composer install --no-interaction
fi

# Frontend build (Vite manifest mora da postoji da stranice ne bi pucale)
if [ ! -f public/build/manifest.json ]; then
    [ -d node_modules ] || { echo "→ npm install..."; npm install; }
    echo "→ npm run build..."
    npm run build
fi

# Baza + storage link
php artisan migrate --force
php artisan storage:link >/dev/null 2>&1 || true
php artisan config:clear >/dev/null

# Admin ruta je tajna i po klijentu - čitaj je iz .env, nikad ne hardkoduj /admin.
ADMIN_PATH=$(grep -E '^ADMIN_PATH=' .env | tail -1 | cut -d= -f2- | tr -d '"')
ADMIN_PATH="${ADMIN_PATH:-admin}"

echo ""
echo "════════════════════════════════════════════════"
if grep -qE '^SITE_FEATURE_TERENI=(true|1)' .env; then
    echo "  Mapa:   http://127.0.0.1:${PORT}/mapa   (i / vodi tu)"
else
    echo "  Sajt:   http://127.0.0.1:${PORT}/sr"
    echo "  Blog:   http://127.0.0.1:${PORT}/blog"
fi
echo "  Admin:  http://127.0.0.1:${PORT}/${ADMIN_PATH}"
echo "════════════════════════════════════════════════"
echo ""

exec php artisan serve --host=127.0.0.1 --port="$PORT"
