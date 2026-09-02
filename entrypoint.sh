#!/bin/sh
set -e

echo "Waiting for MySQL at ${MYSQLHOST:-localhost}:${MYSQLPORT:-3306}..."
for i in $(seq 1 30); do
  if mysqladmin ping -h "${MYSQLHOST:-localhost}" -P "${MYSQLPORT:-3306}" -u "${MYSQLUSER:-root}" -p"${MYSQLPASSWORD:-}" --silent 2>/dev/null; then
    echo "MySQL is up."
    break
  fi
  echo "  still waiting ($i/30)..."
  sleep 2
done

if [ -f /var/www/html/sql/schema.sql ]; then
  echo "Importing schema.sql (safe to repeat — uses CREATE TABLE IF NOT EXISTS)..."
  mysql -h "${MYSQLHOST:-localhost}" -P "${MYSQLPORT:-3306}" -u "${MYSQLUSER:-root}" -p"${MYSQLPASSWORD:-}" "${MYSQLDATABASE}" < /var/www/html/sql/schema.sql \
    && echo "Schema import done." \
    || echo "WARNING: schema import failed — check MYSQL* env vars in Railway."
fi

exec apache2-foreground
