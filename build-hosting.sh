#!/usr/bin/env bash
#
# Builds a ready-to-upload folder for cPanel-style shared hosting (upload with FileZilla).
#
#   bash build-hosting.sh
#
# Output (dist/shared-hosting/):
#   college-app/       → upload OUTSIDE public_html (e.g. /home/USER/college-app)
#   public_html/       → contents go INTO public_html
#   database-mysql.sql
#   README-DEPLOY.txt
#
set -euo pipefail

ROOT="$(cd "$(dirname "$0")" && pwd)"
PHP="${PHP:-/h/laragon/bin/php/php-8.5.6-nts-Win32-vs17-x64/php.exe}"
COMPOSER="${COMPOSER:-/c/ProgramData/ComposerSetup/bin/composer.phar}"
MYSQL_BIN="${MYSQL_BIN:-/h/laragon/bin/mysql/mysql-8.4.3-winx64/bin}"

DIST="$ROOT/dist"
OUT="$DIST/shared-hosting"
APP="$OUT/college-app"
WEB="$OUT/public_html"

step() { printf '\n\033[1;34m==> %s\033[0m\n' "$1"; }

step "Building frontend assets"
cd "$ROOT"
npm run build >/dev/null

step "Preparing folders"
rm -f "$DIST/college-shared-hosting.zip"
mkdir -p "$OUT"
# Empty the output instead of deleting it: FileZilla/Explorer may hold these folders open,
# and Windows refuses to remove a folder that another program is showing.
find "$OUT" -mindepth 1 -type f -delete
find "$OUT" -mindepth 1 -depth -type d -empty -delete 2>/dev/null || true
mkdir -p "$APP" "$WEB"

step "Copying application core"
cp -r app bootstrap config database routes artisan composer.json composer.lock "$APP/"
mkdir -p "$APP/resources"
cp -r resources/views "$APP/resources/"
rm -f "$APP"/bootstrap/cache/*.php "$APP"/database/*.sqlite*
mkdir -p "$APP"/storage/{app/private,app/public,framework/cache/data,framework/sessions,framework/views,logs}
for dir in app/private app/public framework/cache/data framework/sessions framework/views logs; do
    printf '*\n!.gitignore\n' > "$APP/storage/$dir/.gitignore"
done
printf 'Require all denied\nDeny from all\n' > "$APP/.htaccess"

step "Installing production PHP dependencies (no dev packages)"
cd "$APP"
"$PHP" "$COMPOSER" install --no-dev --optimize-autoloader --no-interaction --no-scripts --quiet 2>/dev/null

step "Writing production .env"
APP_KEY="base64:$("$PHP" -r 'echo base64_encode(random_bytes(32));')"
cat > "$APP/.env" <<EOF
APP_NAME="College Management"
APP_ENV=production
APP_KEY=$APP_KEY
APP_DEBUG=false
APP_URL=https://your-domain.com

APP_LOCALE=en
APP_FALLBACK_LOCALE=en

LOG_CHANNEL=single
LOG_LEVEL=error

# Default: SQLite — works without creating a database (file: college-app/database/database.sqlite)
DB_CONNECTION=sqlite

# To use MySQL instead: import database-mysql.sql in phpMyAdmin, then replace the line above with:
# DB_CONNECTION=mysql
# DB_HOST=localhost
# DB_PORT=3306
# DB_DATABASE=cpaneluser_college
# DB_USERNAME=cpaneluser_dbuser
# DB_PASSWORD=your-db-password

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

CACHE_STORE=database
QUEUE_CONNECTION=sync
FILESYSTEM_DISK=local
BROADCAST_CONNECTION=log
MAIL_MAILER=log
EOF

"$PHP" artisan package:discover --ansi >/dev/null

step "Creating seeded SQLite database"
touch "$APP/database/database.sqlite"
"$PHP" artisan migrate --force --seed --no-interaction >/dev/null

step "Creating MySQL dump (for phpMyAdmin import)"
TMP_DB="college_build_$$"
"$MYSQL_BIN/mysql.exe" -uroot -e "CREATE DATABASE $TMP_DB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
DB_CONNECTION=mysql DB_HOST=127.0.0.1 DB_PORT=3306 DB_DATABASE="$TMP_DB" DB_USERNAME=root DB_PASSWORD= \
\
    "$PHP" artisan migrate --force --seed --no-interaction >/dev/null
"$MYSQL_BIN/mysqldump.exe" -uroot --no-tablespaces --skip-comments --set-gtid-purged=OFF \
    --default-character-set=utf8mb4 --ignore-table="$TMP_DB.cache" --ignore-table="$TMP_DB.sessions" \
    "$TMP_DB" > "$OUT/database-mysql.tmp"
"$MYSQL_BIN/mysqldump.exe" -uroot --no-tablespaces --skip-comments --set-gtid-purged=OFF --no-data \
    "$TMP_DB" cache sessions >> "$OUT/database-mysql.tmp"
"$MYSQL_BIN/mysql.exe" -uroot -e "DROP DATABASE $TMP_DB;"
# Strip MySQL-8-only clauses so the dump also imports on MariaDB hosts.
sed -E -e 's/ ?\/\*!80016 DEFAULT ENCRYPTION=.N. \*\///g' -e 's/utf8mb4_0900_ai_ci/utf8mb4_unicode_ci/g' \
    "$OUT/database-mysql.tmp" > "$OUT/database-mysql.sql"
rm "$OUT/database-mysql.tmp"

step "Preparing public_html"
cd "$ROOT"
cp public/.htaccess public/favicon.ico public/robots.txt "$WEB/"
cp -r public/build "$WEB/"
mkdir -p "$WEB/uploads"
cat > "$WEB/uploads/.htaccess" <<'EOF'
# Uploaded files are data only — never run scripts from here.
<FilesMatch "\.(php|phtml|phar|pl|py|cgi|sh)$">
    Require all denied
</FilesMatch>
Options -Indexes
EOF
cat > "$WEB/index.php" <<'EOF'
<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

/*
| Folder that holds the Laravel core, relative to this public_html folder.
| Default layout: /home/USER/college-app next to /home/USER/public_html.
*/
$appPath = __DIR__.'/../college-app';

if (file_exists($maintenance = $appPath.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

require $appPath.'/vendor/autoload.php';

/** @var Application $app */
$app = require_once $appPath.'/bootstrap/app.php';

$app->usePublicPath(__DIR__);

$app->handleRequest(Request::capture());
EOF

step "Writing deployment guide"
cat > "$OUT/README-DEPLOY.txt" <<'EOF'
===============================================================
  শেয়ার্ড হোস্টিং এ FileZilla দিয়ে আপলোডের নিয়ম
===============================================================

সার্ভারের প্রয়োজন:
  - PHP 8.4 বা তার বেশি   (cPanel > MultiPHP Manager / Select PHP Version)
  - PHP extension: pdo_sqlite (বা pdo_mysql), mbstring, openssl, fileinfo

---------------------------------------------------------------
প্রথমবার ইনস্টল
---------------------------------------------------------------
১. FileZilla তে FTP দিয়ে কানেক্ট করুন। ডান দিকে (সার্ভার) হোম ফোল্ডার
   /home/USERNAME/ খুলুন।
   FileZilla মেনু: Server > Force showing hidden files চালু করুন
   (না হলে .htaccess ও .env আপলোড/দেখা যাবে না)।

২. বাম দিক (আপনার কম্পিউটার) থেকে আপলোড করুন:
     college-app ফোল্ডার          →  /home/USERNAME/college-app
     public_html এর ভেতরের সবকিছু  →  /home/USERNAME/public_html/

   গঠন হবে:
     /home/USERNAME/
         college-app/     <- Laravel কোর (ব্রাউজার থেকে দেখা যাবে না)
         public_html/     <- index.php, .htaccess, build/, uploads/

   (vendor ফোল্ডারে অনেক ফাইল, আপলোডে কিছুটা সময় লাগবে।)

৩. college-app/.env খুলে (FileZilla তে রাইট ক্লিক > View/Edit)
   APP_URL এ নিজের ডোমেইন দিন:  APP_URL=https://yourcollege.edu.bd

৪. ডাটাবেস (যেকোনো একটি):
   (ক) SQLite — কিছু করতে হবে না, ডাটাবেস আগে থেকেই তৈরি:
       college-app/database/database.sqlite
   (খ) MySQL — cPanel > MySQL Databases এ ডাটাবেস ও ইউজার বানান,
       phpMyAdmin এ database-mysql.sql Import করুন, তারপর .env এ
       DB_CONNECTION=sqlite লাইনটি মুছে MySQL লাইনগুলোর # সরিয়ে তথ্য দিন।

৫. পারমিশন (FileZilla: রাইট ক্লিক > File permissions > 755,
   "Recurse into subdirectories" টিক দিন):
       college-app/storage
       college-app/bootstrap/cache
       college-app/database        (SQLite হলে; database.sqlite ফাইলটি 664)
       public_html/uploads

৬. ব্রাউজারে https://yourdomain/admin এ সুপার অ্যাডমিন হিসেবে লগইন করুন।
   সিস্টেম পাতায় সব সবুজ/ঠিক আছে কিনা দেখে নিন।

---------------------------------------------------------------
পরে আপডেট করার নিয়ম (নতুন কোড আপলোড)
---------------------------------------------------------------
  আপলোড করুন (overwrite):
     college-app/app, bootstrap/app.php, config, database/migrations,
     database/seeders, resources, routes, vendor, composer.json, composer.lock
     public_html/build  (পুরনো build ফোল্ডার মুছে নতুনটা দিন)

  কখনোই overwrite করবেন না:
     college-app/.env
     college-app/database/database.sqlite   (লাইভ ডাটা!)
     college-app/storage
     public_html/uploads                    (আপলোড করা লোগো/নোটিশ ফাইল)

  আপলোডের পর: অ্যাডমিন > সিস্টেম (মাইগ্রেশন/ক্যাশ) পাতায় গিয়ে
     ১. "মাইগ্রেশন চালান" (বাকি মাইগ্রেশন থাকলে)
     ২. "ক্যাশ পরিষ্কার করুন"
  এই পাতাটি শুধুমাত্র সুপার অ্যাডমিন দেখতে পায়।

---------------------------------------------------------------
সমস্যা হলে
---------------------------------------------------------------
  - 500 Error: college-app/storage/logs/laravel.log দেখুন।
  - "vendor/autoload.php not found": public_html/index.php এর
    $appPath ঠিক ফোল্ডার দেখাচ্ছে কিনা মিলিয়ে নিন।
  - "Composer detected issues in your platform": সার্ভারের PHP 8.4 করুন।
  - college-app ফোল্ডার public_html এর বাইরে রাখা না গেলে
    public_html/college-app এ রেখে index.php তে লিখুন:
        $appPath = __DIR__.'/college-app';
    (college-app/.htaccess ফোল্ডারটি ব্রাউজার থেকে লক রাখবে।)

===============================================================
EOF

printf '
[1;32mDone![0m  Upload this folder with FileZilla: %s
' "$OUT"
