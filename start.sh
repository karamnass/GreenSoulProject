#!/usr/bin/env sh
set -e

cd /var/www/html

php artisan storage:link || true

if [ "${RUN_MIGRATIONS}" = "true" ]; then
  php artisan migrate --force
fi

exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
