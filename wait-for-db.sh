#!/bin/sh
# wait-for-db.sh
set -e
host="$1"
shift

until mysql -h "$host" -u "$DB_USERNAME" -p"$DB_PASSWORD" -e "SELECT 1"; do
  echo "Waiting for MariaDB at $host..."
  sleep 2
done

exec "$@"
