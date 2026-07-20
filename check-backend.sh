#!/usr/bin/env bash
#
# Provera da li backend radi.
#
#   ./check-backend.sh          → brza provera (preduslovi, baza, HTTP rute)
#   ./check-backend.sh --full   → + kompletan test suite (php artisan test)
#
set -u
cd "$(dirname "$0")"

PORT="${CHECK_PORT:-8123}"
BASE="http://127.0.0.1:${PORT}"
PASS=0
FAIL=0

ok()  { printf '  \033[32m✔\033[0m %s\n' "$1"; PASS=$((PASS + 1)); }
bad() { printf '  \033[31m✘\033[0m %s\n' "$1"; FAIL=$((FAIL + 1)); }

echo "== Preduslovi =="
php -m | grep -q pdo_mysql && ok "PHP pdo_mysql ekstenzija" || bad "PHP pdo_mysql ekstenzija nedostaje"
[ -f .env ] && ok ".env postoji" || bad ".env ne postoji (pokreni ./start.sh)"
[ -d vendor ] && ok "composer zavisnosti instalirane" || bad "vendor/ ne postoji (composer install)"
[ -f public/build/manifest.json ] && ok "frontend build postoji" || bad "public/build ne postoji (npm run build)"

echo "== Baza =="
if php artisan migrate:status >/dev/null 2>&1; then
    ok "konekcija na MySQL + migracije"
else
    bad "ne mogu da se povežem na bazu (proveri DB_* u .env)"
fi

echo "== HTTP rute =="
php artisan serve --host=127.0.0.1 --port="$PORT" >/dev/null 2>&1 &
SERVER_PID=$!
trap 'kill "$SERVER_PID" 2>/dev/null' EXIT

# sačekaj da se server podigne
for _ in $(seq 1 20); do
    curl -s -o /dev/null "$BASE/up" && break
    sleep 0.3
done

check_url() {
    local url="$1" expected="$2" code
    code=$(curl -s -o /dev/null -w '%{http_code}' "$BASE$url")
    if [ "$code" = "$expected" ]; then
        ok "$url → $code"
    else
        bad "$url → $code (očekivano $expected)"
    fi
}

check_url /up 200
check_url / 302
check_url /sr 200
check_url /en 200
check_url /blog 200
check_url /sitemap.xml 200
check_url /admin/login 200

curl -s "$BASE/sr" | grep -q 'Pravna sigurnost' \
    && ok "početna renderuje sadržaj" \
    || bad "početna ne renderuje očekivani sadržaj"

curl -s "$BASE/admin/login" | grep -q 'Lozinka' \
    && ok "admin login renderuje (prevodi rade)" \
    || bad "admin login ne renderuje ispravno"

if [ "${1:-}" = "--full" ]; then
    echo "== Feature testovi =="
    if php artisan test --compact; then
        ok "php artisan test"
    else
        bad "php artisan test (vidi ispis iznad)"
    fi
fi

echo ""
echo "Rezultat: ${PASS} OK, ${FAIL} neuspešno"
[ "$FAIL" -eq 0 ] && echo "✅ Backend radi." || echo "❌ Ima problema — vidi iznad."
exit "$FAIL"
