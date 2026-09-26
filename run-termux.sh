#!/data/data/com.termux/files/usr/bin/bash
set -euo pipefail

BASE="$(cd "$(dirname "$0")" && pwd)"
APP="$BASE/entiredust_project"
ENV_FILE="$APP/.env"
cd "$APP"

echo "=== INFOROVA setup ==="
pkg install php mariadb -y

if ! pgrep -x mariadbd >/dev/null 2>&1; then
    if command -v mariadbd-safe >/dev/null 2>&1; then
        mariadbd-safe >/dev/null 2>&1 &
    else
        mysqld_safe >/dev/null 2>&1 &
    fi
    for _ in $(seq 1 20); do
        mariadb-admin -h 127.0.0.1 -P 3306 ping >/dev/null 2>&1 && break
        sleep 1
    done
fi

if [[ ! -f "$ENV_FILE" ]]; then
    echo "Create $ENV_FILE with the INFOROVA database credentials and the SAME ED_SHARED_SECRET used by BDXLink."
    umask 077
    cat > "$ENV_FILE" <<'EOF'
DB_HOST=127.0.0.1
DB_NAME=inforova
DB_USER=inforova_user
DB_PASS=change_me
DB_PORT=3306
ED_SHARED_SECRET=replace_with_the_same_long_random_secret_used_by_BDXLink
APP_URL=http://127.0.0.1:8001
EOF
    chmod 600 "$ENV_FILE"
    echo "Edit $ENV_FILE with real credentials, then run this script again."
    exit 1
fi

set -a
source "$ENV_FILE"
set +a

if [[ -z "${ED_SHARED_SECRET:-}" || ${#ED_SHARED_SECRET} -lt 32 || "$ED_SHARED_SECRET" == replace_with_* ]]; then
    echo "ED_SHARED_SECRET is required and must exactly match BDXLink."
    exit 1
fi

if [[ -z "${DB_PASS:-}" || "$DB_PASS" == "change_me" ]]; then
    echo "DB_PASS must be configured in $ENV_FILE."
    exit 1
fi

if ! mariadb -h "${DB_HOST:-127.0.0.1}" -P "${DB_PORT:-3306}" -u "${DB_USER:-inforova_user}" -p"${DB_PASS}" "${DB_NAME:-inforova}" -e "SELECT 1;" >/dev/null 2>&1; then
    echo "Saved INFOROVA database credentials are not working."
    exit 1
fi

php -r 'putenv("ED_SHARED_SECRET=".getenv("ED_SHARED_SECRET")); require __DIR__."/config/app.php"; require __DIR__."/config/database.php"; app_secret(); echo "DB + secret OK
";'

php -S 0.0.0.0:8001
