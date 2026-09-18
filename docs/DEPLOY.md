# Deploy na produkciju

Svaki push na `main` (ili rucni **Run workflow**) pokrece
[`.github/workflows/deploy.yml`](../.github/workflows/deploy.yml):

1. **Provera secreta** — staje odmah ako neki nedostaje.
2. **Rsync** repoa na server (bez `.env`, `storage/`, `vendor/`, `node_modules/`,
   `public/build`, `public/storage` i `database/database.sqlite`).
3. **Rebuild** preko SSH-a: [`bin/deploy-production.sh`](../bin/deploy-production.sh)
   radi `composer install`, `npm ci && npm run build`, `php artisan migrate --force`,
   `php artisan optimize`, prava nad fajlovima i restart php-fpm-a. Sajt je u
   maintenance modu (`artisan down`) dok traje.
4. **Smoke test** — `DEPLOY_URL` mora da vrati HTTP 200.

Dva deploya nikad ne idu paralelno (`concurrency: production-deploy`).

## Repo secrets

Settings → Secrets and variables → Actions → **New repository secret**:

| Secret | Primer | Sta je |
|---|---|---|
| `DEPLOY_HOST` | `1.2.3.4` | IP ili hostname servera |
| `DEPLOY_USER` | `root` | SSH nalog; mora da sme `chown`, `systemctl restart` |
| `DEPLOY_PATH` | `/var/www/tereni.example.rs` | apsolutna putanja aplikacije na serveru |
| `DEPLOY_SSH_KEY` | ceo sadrzaj privatnog kljuca | deploy kljuc, bez passphrase |
| `DEPLOY_KNOWN_HOSTS` | izlaz `ssh-keyscan -H <host>` | host key, sprecava MITM |
| `DEPLOY_URL` | `https://tereni.example.rs` | adresa za smoke test |

Kljuc i host key se prave lokalno:

```bash
ssh-keygen -t ed25519 -f ~/.ssh/tereni_deploy -N '' -C 'github-actions-tereni'
cat ~/.ssh/tereni_deploy        # -> DEPLOY_SSH_KEY
ssh-keyscan -H <host>           # -> DEPLOY_KNOWN_HOSTS
```

Javni deo (`~/.ssh/tereni_deploy.pub`) ide u `~/.ssh/authorized_keys` naloga
`DEPLOY_USER` na serveru.

Brze, preko `gh` CLI-ja iz korena repoa:

```bash
gh secret set DEPLOY_HOST        --body '1.2.3.4'
gh secret set DEPLOY_USER        --body 'root'
gh secret set DEPLOY_PATH        --body '/var/www/tereni.example.rs'
gh secret set DEPLOY_URL         --body 'https://tereni.example.rs'
gh secret set DEPLOY_SSH_KEY     < ~/.ssh/tereni_deploy
ssh-keyscan -H 1.2.3.4 | gh secret set DEPLOY_KNOWN_HOSTS
gh secret list                   # provera: svih 6
```

Repo je javan, pa server podaci smeju samo ovde — nikad u kod.

## Prvi deploy — sta mora da postoji na serveru

```bash
# 1. kod i zavisnosti
mkdir -p /var/www/tereni.example.rs && cd /var/www/tereni.example.rs
git clone <repo> . && composer install --no-dev && npm ci && npm run build

# 2. .env (rsync ga nikad ne dira)
cp .env.example .env
php artisan key:generate
# APP_ENV=production, APP_DEBUG=false, tacan APP_URL, nasumican ADMIN_PATH, MAIL_*

# 3. SQLite baza (takodje van rsync-a)
touch database/database.sqlite
php artisan migrate --force --seed
php artisan storage:link

# 4. prava
chown -R www-data:www-data . && chmod -R 775 storage bootstrap/cache
```

Na serveru jos treba: PHP 8.2+ sa `php-fpm`, Composer, Node 20+, `rsync`,
web server (nginx/Apache) sa rootom na `public/` i HTTPS sertifikatom.

## Kad deploy pukne

- **Provera secreta** — dodaj secret koji je ispisan u gresci.
- **Permission denied (publickey)** — javni kljuc nije u `authorized_keys`,
  ili `DEPLOY_USER` nije tacan.
- **Host key verification failed** — `DEPLOY_KNOWN_HOSTS` je zastareo; ponovo
  pokreni `ssh-keyscan`.
- **GRESKA: nema .env / database.sqlite** — preskocen je prvi deploy iznad.
- **Smoke test != 200** — kod je vec na serveru; pogledaj `storage/logs/laravel.log`.
- Sajt ostao u maintenance modu: `php artisan up` na serveru.
