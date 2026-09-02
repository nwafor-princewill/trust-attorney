#!/bin/sh
set -e

# Railway names the database variable MYSQL_DATABASE (with underscore) while
# the other MySQL vars have no underscore — support both spellings so this
# works whichever the plugin gives us.
DBNAME="${MYSQLDATABASE:-${MYSQL_DATABASE:-}}"

echo "Waiting for MySQL at ${MYSQLHOST:-localhost}:${MYSQLPORT:-3306}..."
for i in $(seq 1 30); do
  if mysqladmin ping -h "${MYSQLHOST:-localhost}" -P "${MYSQLPORT:-3306}" -u "${MYSQLUSER:-root}" -p"${MYSQLPASSWORD:-}" --skip-ssl --silent 2>/dev/null; then
    echo "MySQL is up."
    break
  fi
  echo "  still waiting ($i/30)..."
  sleep 2
done

if [ -f /var/www/html/sql/schema.sql ]; then
  echo "Importing schema.sql (safe to repeat — uses CREATE TABLE IF NOT EXISTS)..."
  mysql -h "${MYSQLHOST:-localhost}" -P "${MYSQLPORT:-3306}" -u "${MYSQLUSER:-root}" -p"${MYSQLPASSWORD:-}" --skip-ssl "${DBNAME}" < /var/www/html/sql/schema.sql \
    && echo "Schema import done." \
    || echo "WARNING: schema import failed — check MYSQL* env vars in Railway."
fi

exec apache2-foreground
