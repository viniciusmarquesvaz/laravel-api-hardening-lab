#!/bin/sh
set -eu

if [ -z "${APP_KEY:-}" ]; then
    export APP_KEY="$(php artisan key:generate --show --no-ansi)"
fi

php artisan migrate --force

exec "$@"
