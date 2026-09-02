#!/bin/sh
set -e

# Debug: Show which MPM modules are enabled
echo "=== Currently enabled MPM modules ==="
ls -la /etc/apache2/mods-enabled/ | grep mpm || echo "No MPM modules found in mods-enabled"

# Force disable all MPMs and enable only prefork (redundant safety)
a2dismod mpm_event mpm_worker mpm_prefork 2>/dev/null || true
a2enmod mpm_prefork

# Railway names the database variable MYSQL_DATABASE (with underscore) while
# the other MySQL vars have no underscore — support both spellings so this
# works whichever the plugin gives us.
DBNAME="${MYSQL_DATABASE:-${MYSQLDATABASE:-}}"

# Use the correct variable names from Railway
MYSQL_HOST="${MYSQLHOST:-localhost}"
MYSQL_PORT="${MYSQLPORT:-3306}"
MYSQL_USER="${MYSQLUSER:-root}"
MYSQL_PASS="${MYSQLPASSWORD:-}"

echo "Waiting for MySQL at ${MYSQL_HOST}:${MYSQL_PORT}..."
for i in $(seq 1 30); do
  if mysqladmin ping -h "${MYSQL_HOST}" -P "${MYSQL_PORT}" -u "${MYSQL_USER}" -p"${MYSQL_PASS}" --skip-ssl --silent 2>/dev/null; then
    echo "MySQL is up."
    break
  fi
  echo "  still waiting ($i/30)..."
  sleep 2
done

if [ -f /var/www/html/sql/schema.sql ]; then
  echo "Importing schema.sql (safe to repeat — uses CREATE TABLE IF NOT EXISTS)..."
  mysql -h "${MYSQL_HOST}" -P "${MYSQL_PORT}" -u "${MYSQL_USER}" -p"${MYSQL_PASS}" --skip-ssl "${DBNAME}" < /var/www/html/sql/schema.sql \
    && echo "Schema import done." \
    || echo "WARNING: schema import failed — check MYSQL* env vars in Railway."
fi

echo "=== MPM configuration after cleanup ==="
apache2ctl -M | grep mpm

exec apache2-foreground