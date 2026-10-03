# TravelAI Nepal — Deployment Guide

**Audience:** Owner / DevOps / Developer
**Version:** 1.0
**Last Updated:** 2026-09-30
**Status:** Authoritative deployment reference

> **Note:** All commands are bash/SSH (server side) unless stated otherwise. Local commands = Windows CMD (Laragon).

---

## 1. Prerequisites

### 1.1 Server

**Recommended:** Oracle Cloud Always Free Tier (R23 compliant)

| Item | Requirement |
|------|-------------|
| CPU | 4 ARM cores (Ampere A1) |
| RAM | 24 GB |
| Storage | 200 GB |
| OS | Ubuntu 22.04 LTS |
| Bandwidth | 10 TB outbound / month |

**Alternative (paid):** Laravel Cloud ($20/mo), Hetzner VPS ($4.5/mo) — requires R23 exception.

### 1.2 Domain

**Primary:** `travelainepal.com` (Spaceship — ~$9/yr)

**DNS Records needed:**
- A record: `@` → server IP
- A record: `www` → server IP (कि CNAME to `@`)

### 1.3 Local Tools

- SSH client (PuTTY / OpenSSH)
- Git (installed)
- Text editor (for `.env`)

### 1.4 Credentials / Access

- Oracle Cloud account
- SSH private key (`.pem` कि `.key`)
- GitHub repository access
- Domain registrar login (Spaceship)

---

## 2. Server Setup (Oracle Cloud Free Tier)

### 2.1 Create Instance

**Step 1:** Login to Oracle Cloud Console: https://cloud.oracle.com

**Step 2:** Navigate → Compute → Instances → Create Instance.

**Step 3:** Configure:
Name: travelai-prod
Image: Canonical Ubuntu 22.04
Shape: VM.Standard.A1.Flex (ARM)
OCPU: 4
Memory: 24 GB
Boot volume: 200 GB
SSH keys: Upload your public key (.pub)
Networking: Create new VCN (default OK)

text

**Step 4:** Click **Create**. Wait ~2 min.

**Step 5:** Note the **Public IP Address** shown on the instance details page.

### 2.2 Open Firewall Ports

**Step 1:** Navigate → Networking → VCN → Security Lists → Default.

**Step 2:** Add Ingress Rules:
Source: 0.0.0.0/0
TCP: 22 (SSH)
TCP: 80 (HTTP)
TCP: 443 (HTTPS)

text

**Step 3:** Save.

### 2.3 SSH Access

**From local Windows CMD:**
```bash
ssh -i path/to/private-key.key ubuntu@<PUBLIC_IP>
First login → update:

sudo apt update && sudo apt upgrade -y
2.4 Configure iptables (Oracle-specific)
Oracle instances block ports via iptables by default.

Add rules:

bash
sudo iptables -I INPUT 6 -m state --state NEW -p tcp --dport 80 -j ACCEPT
sudo iptables -I INPUT 6 -m state --state NEW -p tcp --dport 443 -j ACCEPT
sudo netfilter-persistent save
Verify:

bash
sudo iptables -L INPUT -n
3. Install Dependencies
3.1 PHP 8.4
bash
sudo apt install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

sudo apt install -y php8.4-fpm php8.4-cli php8.4-mysql php8.4-mbstring \
  php8.4-xml php8.4-curl php8.4-zip php8.4-bcmath php8.4-gd \
  php8.4-intl php8.4-redis php8.4-sqlite3

php -v
Expected: PHP 8.4.x

3.2 MySQL 8.0
bash
sudo apt install -y mysql-server
sudo mysql_secure_installation
Prompts:

VALIDATE PASSWORD: N (for simplicity — कि set strong)

Remove anonymous users: Y

Disallow root login remotely: Y

Remove test database: Y

Reload privilege tables: Y

Create database + user:

bash
sudo mysql
MySQL prompt:

sql
CREATE DATABASE travelai_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'travelai'@'localhost' IDENTIFIED BY 'STRONG_PASSWORD_HERE';
GRANT ALL PRIVILEGES ON travelai_db.* TO 'travelai'@'localhost';
FLUSH PRIVILEGES;
EXIT;
3.3 Nginx
bash
sudo apt install -y nginx
sudo systemctl enable nginx
sudo systemctl start nginx
3.4 Composer
bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
composer --version
3.5 Node.js 18+
bash
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install -y nodejs
node -v
npm -v
3.6 Git
bash
sudo apt install -y git
git --version
3.7 Supervisor (queue worker)
bash
sudo apt install -y supervisor
sudo systemctl enable supervisor
sudo systemctl start supervisor
4. Application Deploy
4.1 Clone Repository
bash
cd /var/www
sudo git clone https://github.com/DigiSewaAI/TravelAI-Nepal.git
sudo chown -R ubuntu:ubuntu TravelAI-Nepal
cd TravelAI-Nepal
4.2 Install PHP Dependencies
bash
composer install --no-dev --optimize-autoloader
4.3 Install Node Dependencies + Build
bash
npm ci
npm run build
4.4 Configure .env
bash
cp .env.example .env
nano .env
Required production values (edit this section):

env
APP_NAME="TravelAI Nepal"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://travelainepal.com
APP_TIMEZONE=UTC
APP_KEY=

LOG_LEVEL=warning

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=travelai_db
DB_USERNAME=travelai
DB_PASSWORD=STRONG_PASSWORD_HERE

SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
SESSION_ENCRYPT=true

QUEUE_CONNECTION=database
CACHE_STORE=database
FILESYSTEM_DISK=local

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=travelainepal@gmail.com
MAIL_PASSWORD="your-app-password"
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="travelainepal@gmail.com"
MAIL_FROM_NAME="TravelAI Nepal"

# AI Providers
GROQ_API_KEY=
GROQ_API_KEYS=
GROQ_MODEL=qwen/qwen3.8-27b
GROQ_BASE_URL=https://api.groq.com/openai/v1

OPENROUTER_API_KEY=
OPENROUTER_API_KEYS=
OPENROUTER_MODEL=meta-llama/llama-3.1-8b-instruct:free
OPENROUTER_BASE_URL=https://openrouter.ai/api/v1

CEREBRAS_API_KEY=
CEREBRAS_API_KEYS=
CEREBRAS_MODEL=llama3.1-8b
CEREBRAS_BASE_URL=https://api.cerebras.ai/v1

AI_FALLBACK_ENABLED=true

EXCHANGE_RATE_USD_NPR=133

OPENWEATHER_API_KEY=

VITE_APP_NAME="${APP_NAME}"
Save: Ctrl+O, Enter, Ctrl+X.

4.5 Generate App Key
bash
php artisan key:generate
4.6 Run Migrations
bash
php artisan migrate --force
Expected: All migrations run successfully.
### 4.6b Path 3A Migrations (Products)

Path 3A adds:
- `products` table (base — polymorphic: shop/rental/wholesale)
- `shop_details`, `rental_details`, `wholesale_details`
- 3 categories (shop, rental, wholesale)
- 3 provider types (shop-owner, rental-provider, wholesale-provider)
- 3 pivot mappings

These run automatically via:
```bash
php artisan migrate --force
No separate command needed. Existing 10-category system is unaffected (additive only — R8).


### 4.7 Run Production Seeders

**Env setup (production `.env`) — add these:**
```
ADMIN_EMAIL=parasharregmi@gmail.com
ADMIN_NAME=Parashar Regmi
ADMIN_PASSWORD=Himalayan@1980
ADMIN_PHONE=9761762036
```

**Run:**
```bash
php artisan db:seed --force
```

**Behavior (env-aware):**

- ✅ Runs: Core data (categories, plans, locations)
- ✅ Runs: Route data (routes, waypoints, segments)
- ✅ Runs: ProductionAdminSeeder (Parashar Regmi — super_admin)
- ✅ Runs: RealEntitiesSeeder (Anju + Pareen + John — providers/services/bookings)
- ✅ Runs: DemoProvidersSeeder (5 demo providers — Hotel, Activity, Experience, Resort, Homestay)
- ❌ Skips: 12 provider seeders (env guard)
- ❌ Skips: Service + Tourism seeders (env guard)
- ❌ Skips: AssignProviderTypes (env guard)

**Result:** Fresh DB + 4 users + 7 providers + ~15 services + 3 bookings.

**Post-seed:** Verify admin login with ADMIN_EMAIL + ADMIN_PASSWORD.

**Full strategy:** `docs/PRODUCTION_SEEDERS_SUMMARY.md`

4.8 Create Storage Link
bash
php artisan storage:link
4.9 Set Permissions
bash
sudo chown -R www-data:www-data /var/www/TravelAI-Nepal
sudo chmod -R 755 /var/www/TravelAI-Nepal
sudo chmod -R 775 /var/www/TravelAI-Nepal/storage
sudo chmod -R 775 /var/www/TravelAI-Nepal/bootstrap/cache
4.10 Cache Config / Routes / Views
bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
Verify:

bash
php artisan optimize

---

## 5. Queue + Scheduler Setup

### 5.1 Supervisor Config (Queue Worker)

**Create config:**
```bash
sudo nano /etc/supervisor/conf.d/travelai-worker.conf
Paste:

ini
[program:travelai-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/TravelAI-Nepal/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/TravelAI-Nepal/storage/logs/worker.log
stopwaitsecs=3600
Save: Ctrl+O, Enter, Ctrl+X.

Enable + start:

bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start travelai-worker:*
Check status:

bash
sudo supervisorctl status
5.2 Cron (Scheduler)
Add cron entry:

bash
sudo crontab -e -u www-data
Add line:

text
* * * * * cd /var/www/TravelAI-Nepal && php artisan schedule:run >> /dev/null 2>&1
Save + exit.

Verify:

bash
sudo crontab -l -u www-data
Reference: docs/deployment/QUEUE_SCHEDULER.md

6. Nginx Configuration
6.1 Site Config
Create config:

bash
sudo nano /etc/nginx/sites-available/travelainepal
Paste:

nginx
server {
    listen 80;
    listen [::]:80;
    server_name travelainepal.com www.travelainepal.com;
    root /var/www/TravelAI-Nepal/public;

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    client_max_body_size 20M;
}
Save.

6.2 Enable Site
bash
sudo ln -s /etc/nginx/sites-available/travelainepal /etc/nginx/sites-enabled/
sudo rm /etc/nginx/sites-enabled/default
sudo nginx -t
sudo systemctl reload nginx
Expected: No syntax errors.

7. Domain + SSL
7.1 DNS Setup (Spaceship)
Login to Spaceship → Domain → DNS:

Add records:

text
Type    Host    Value               TTL
A       @       <SERVER_IP>         3600
A       www     <SERVER_IP>         3600
Wait ~5-30 min for propagation.

Verify:

cmd
nslookup travelainepal.com
Expected: Shows server IP.

7.2 SSL Certificate (Let's Encrypt)
bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d travelainepal.com -d www.travelainepal.com
Prompts:

Email: your email

Agree to terms: Y

Share email with EFF: N (optional)

Certbot auto-edits Nginx config for HTTPS.

Verify auto-renewal:

bash
sudo certbot renew --dry-run
7.3 HTTPS Redirect (Verify)
Check Nginx config:

bash
sudo cat /etc/nginx/sites-available/travelainepal | grep -A 3 "listen 80"
Expected: Should redirect HTTP → HTTPS.

8. Post-Deploy Verification
8.1 Basic Checks
From browser:

Check	URL	Expected
Homepage	https://travelainepal.com	Loads, HTTP → HTTPS
Login page	/login	Loads
Admin login	/admin/dashboard	Redirects to login → works
AI Planner	Homepage → Planner form	Generates itinerary
Provider reg	/register	Form loads
8.2 Functional Checks (After Login)
As Admin:

□ Dashboard loads
□ Payments queue accessible
□ Provider list visible
□ Users list visible
As Provider (test account):

□ Dashboard loads
□ Create service works
□ Payment methods settings accessible
□ Subscription page loads
As Traveler:

□ Dashboard loads
□ Create booking works
□ Journey replay accessible
8.3 Email Test
bash
php artisan tinker
Tinker:

php
Mail::raw('Deploy test', fn($m) => $m->to('your-email@example.com')->subject('Deploy Test'));
exit
Expected: Email received।

8.4 Queue Test
bash
php artisan tinker
Tinker:

php
dispatch(new App\Jobs\ExpireSubscriptionsJob);
exit
Check worker log:

bash
tail -n 20 /var/www/TravelAI-Nepal/storage/logs/worker.log
Expected: Job processed.

8.5 Scheduler Test
Wait 1-2 minutes → check:

bash
grep "schedule:run" /var/log/syslog | tail -5
Expected: Scheduler executed.

8.6 Test Suite (Optional)
bash
cd /var/www/TravelAI-Nepal
php artisan test
Expected: 44 passed / 0 failed

9. Troubleshooting
9.1 Common Issues
Issue: 500 error on homepage

bash
tail -n 50 /var/www/TravelAI-Nepal/storage/logs/laravel.log
Check .env values

Check file permissions

Check APP_KEY set

Issue: 502 Bad Gateway

PHP-FPM not running: sudo systemctl restart php8.4-fpm

Check PHP-FPM socket path: ls /var/run/php/

Issue: Static assets (CSS/JS) not loading

bash
ls /var/www/TravelAI-Nepal/public/build/
Re-run: npm run build

Check permissions on public/build/

Issue: Storage images 404

bash
php artisan storage:link
sudo chmod -R 755 /var/www/TravelAI-Nepal/storage
Issue: Queue jobs not processing

bash
sudo supervisorctl status
sudo supervisorctl restart travelai-worker:*
tail -n 50 storage/logs/worker.log
Issue: Emails not sending

Check .env MAIL_* settings

Test SMTP: php artisan tinker → Mail::raw('test', fn($m) => $m->to(...)->subject('test'));

Gmail: Ensure App Password (not regular password)

Issue: Scheduler not running

bash
sudo crontab -l -u www-data
sudo systemctl status cron
9.2 Log Locations
Log	Location
Application	/var/www/TravelAI-Nepal/storage/logs/laravel.log
Queue worker	/var/www/TravelAI-Nepal/storage/logs/worker.log
Nginx access	/var/log/nginx/access.log
Nginx error	/var/log/nginx/error.log
PHP-FPM	/var/log/php8.4-fpm.log
Supervisor	/var/log/supervisor/supervisord.log
Syslog	/var/log/syslog
9.3 Rollback
Restore from backup:

bash
mysql -u travelai -p travelai_db < /path/to/backup_YYYY_MM_DD.sql
Redeploy previous commit:

bash
cd /var/www/TravelAI-Nepal
git log --oneline -5
git checkout <PREVIOUS_COMMIT_HASH>
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
sudo systemctl reload php8.4-fpm
9.4 Emergency Commands
bash
# Clear all caches
php artisan optimize:clear

# Restart all services
sudo systemctl restart nginx php8.4-fpm supervisor

# Check disk space
df -h

# Check memory
free -h

# Restart server (last resort)
sudo reboot
10. Update / Redeploy Process
For subsequent deployments:

bash
cd /var/www/TravelAI-Nepal

# Pull latest
git pull origin main

# Update dependencies
composer install --no-dev --optimize-autoloader
npm ci && npm run build

# Run new migrations
php artisan migrate --force

# Clear + rebuild caches
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart queue workers (code changed)
sudo supervisorctl restart travelai-worker:*

# Reload PHP-FPM
sudo systemctl reload php8.4-fpm
Verify:

bash
php artisan optimize
tail -n 20 storage/logs/laravel.log
11. Alternative: Laravel Cloud (Paid)
For R23 exception (paid path):

Steps:

Sign up: https://cloud.laravel.com

Connect GitHub repository

Configure environment variables

Deploy (auto-built)

Custom domain (added via dashboard)

SSL auto-provisioned

Cost: ~$20/mo + usage

Trade-offs:

✅ Zero server management

✅ Auto-scaling

✅ Zero-downtime deploys

❌ Not free (R23 violation)

= Only if Oracle Cloud setup fails कि R23 exception granted.

12. Deploy Checklist
Pre-deploy:

□ Server created (Oracle Cloud)
□ Domain purchased (Spaceship)
□ DNS configured
□ SSH access verified
□ Repository access confirmed
Deploy:

□ Dependencies installed (PHP, MySQL, Nginx, Composer, Node, Git, Supervisor)
□ Code cloned
□ .env configured (all values set)
□ APP_KEY generated
□ Migrations run
□ Production seeders run
□ Storage link created
□ Permissions set
□ Config/route/view caches built
□ Supervisor (queue) running
□ Cron (scheduler) added
□ Nginx site enabled
□ SSL certificate installed
Post-deploy:

□ Homepage loads
□ Login works (all 3 roles)
□ AI planner generates
□ Payment flow (verify/reject) works
□ Email sends
□ Queue processes
□ Scheduler runs
□ Suite passes (44p/0f)
Commit + tag:

□ Deployment commit pushed
□ Git tag created (e.g., v1.0.0-deploy)
End of Deployment Guide v1.0