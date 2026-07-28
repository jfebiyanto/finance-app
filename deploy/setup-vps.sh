#!/bin/bash
set -e

echo "============================================"
echo "  Finance App - VPS Deployment"
echo "  (with Cloudflare Tunnel)"
echo "============================================"

# --- Prerequisites ---
command -v git >/dev/null 2>&1 || { echo "Error: git is required"; exit 1; }
command -v docker >/dev/null 2>&1 || { echo "Error: Docker is required"; exit 1; }
command -v docker compose >/dev/null 2>&1 || { echo "Error: Docker Compose is required"; exit 1; }

# --- Clone or pull repository ---
if [ ! -d "finance-app" ]; then
    echo "[1/6] Cloning repository..."
    git clone https://github.com/YOUR_USERNAME/finance-app.git
    cd finance-app
else
    echo "[1/6] Pulling latest changes..."
    cd finance-app
    git pull
fi

# --- Create .env file ---
echo "[2/6] Creating .env file..."

if [ ! -f ".env" ]; then
    cat > .env << EOF
# Application
APP_KEY=
APP_URL=http://localhost:8080

# Database (auto-generated random passwords)
DB_PASSWORD=$(openssl rand -base64 24)
DB_ROOT_PASSWORD=$(openssl rand -base64 24)

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_FROM_ADDRESS=
MAIL_FROM_NAME="FinanceApp"
EOF

    echo "  ✅ .env file created with auto-generated DB passwords!"
    echo ""
    echo "  ⚠️  IMPORTANT: Edit .env and set mail credentials:"
    echo "      - MAIL_USERNAME=j.febiyanto@gmail.com"
    echo "      - MAIL_PASSWORD=your-gmail-app-password"
    echo "      - MAIL_FROM_ADDRESS=j.febiyanto@gmail.com"
    echo ""
    read -p "  Press Enter after editing .env file..."
else
    echo "  .env file already exists, skipping."
fi

# --- Generate APP_KEY if empty ---
echo "[3/6] Checking APP_KEY..."
if grep -q "APP_KEY=$" .env || [ "$(grep '^APP_KEY=' .env | cut -d= -f2)" = "" ]; then
    echo "  Generating APP_KEY..."
    docker run --rm -v "$(pwd):/var/www/html" php:8.4-cli-alpine \
        php /var/www/html/artisan key:generate --force
    echo "  ✅ APP_KEY generated!"
else
    echo "  ✅ APP_KEY already set, skipping."
fi

# --- Build and start containers ---
echo "[4/6] Building and starting containers..."
docker compose -f deploy/docker-compose.prod.yml --env-file .env up -d --build
echo "  ✅ Containers started."

# --- Wait for tunnel to come up and get URL ---
echo "[5/6] Waiting for Cloudflare Tunnel to connect..."
TUNNEL_URL=""
MAX_ATTEMPTS=30
for i in $(seq 1 $MAX_ATTEMPTS); do
    TUNNEL_LOG=$(docker logs finance-tunnel 2>&1 | grep -oE 'https://[a-zA-Z0-9.-]+\.trycloudflare\.com' | tail -1)
    if [ -n "$TUNNEL_LOG" ]; then
        TUNNEL_URL="$TUNNEL_LOG"
        break
    fi
    sleep 2
done

if [ -n "$TUNNEL_URL" ]; then
    echo "  ✅ Tunnel URL: ${TUNNEL_URL}"

    # Update APP_URL in .env to use the tunnel URL (with https)
    TUNNEL_URL_CLEAN=$(echo "$TUNNEL_URL" | sed 's|https://||')
    if grep -q "APP_URL=" .env; then
        sed -i "s|^APP_URL=.*|APP_URL=https://${TUNNEL_URL_CLEAN}|" .env
        echo "  ✅ APP_URL updated to ${TUNNEL_URL} in .env"
    fi

    # Restart app to pick up new APP_URL
    docker compose -f deploy/docker-compose.prod.yml --env-file .env restart app
else
    echo "  ⚠️  Tunnel URL not detected yet. Check status with:"
    echo "     docker logs finance-tunnel"
fi

echo ""
echo "[6/6] Deployment complete!"
echo "============================================"
echo ""
echo "  🔒 Your app is live via Cloudflare Tunnel:"
echo "     ${TUNNEL_URL:-Check 'docker logs finance-tunnel' for the URL}"
echo "     (HTTPS is handled automatically by Cloudflare)"
echo ""
echo "  ⚠️  Important notes:"
echo "     - No ports are exposed to the internet"
echo "     - The tunnel URL changes on restart (quick tunnel mode)"
echo "     - For a permanent URL, set up a named tunnel:"
echo "       https://developers.cloudflare.com/cloudflare-one/connections/connect-networks/"
echo ""
echo "  📧 Daily expense report will be sent at 23:59"
echo ""
echo "  📋 Useful commands:"
echo "     View app logs:       docker compose -f deploy/docker-compose.prod.yml logs -f app"
echo "     View tunnel logs:    docker logs finance-tunnel"
echo "     Stop everything:     docker compose -f deploy/docker-compose.prod.yml down"
echo "     Rebuild & restart:   docker compose -f deploy/docker-compose.prod.yml up -d --build"
echo ""
