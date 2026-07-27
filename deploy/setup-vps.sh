#!/bin/bash
set -e

echo "==================================="
echo "  Finance App - VPS Deployment"
echo "==================================="

# Check prerequisites
command -v git >/dev/null 2>&1 || { echo "Error: git is required"; exit 1; }
command -v docker >/dev/null 2>&1 || { echo "Error: Docker is required"; exit 1; }
command -v docker compose >/dev/null 2>&1 || { echo "Error: Docker Compose is required"; exit 1; }

# --- Get VPS IP ---
VPS_IP=$(curl -s ifconfig.me 2>/dev/null || hostname -I 2>/dev/null | awk '{print $1}')
echo ""
echo "  Deploying to VPS IP: $VPS_IP"
echo ""

# --- Clone or pull repository ---
if [ ! -d "finance-app" ]; then
    echo "[1/5] Cloning repository..."
    git clone https://github.com/YOUR_USERNAME/finance-app.git
    cd finance-app
else
    echo "[1/5] Pulling latest changes..."
    cd finance-app
    git pull
fi

# --- Create .env file ---
echo "[2/5] Creating .env file..."

if [ ! -f ".env" ]; then
    cat > .env << EOF
# Application
APP_KEY=
APP_URL=http://${VPS_IP}

# Database
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

    echo "  .env file created!"
    echo "  !!! IMPORTANT: Edit .env and set:"
    echo "      - MAIL_USERNAME=j.febiyanto@gmail.com"
    echo "      - MAIL_PASSWORD=your-app-password"
    echo "      - MAIL_FROM_ADDRESS=j.febiyanto@gmail.com"
    echo ""
    read -p "  Press Enter after editing .env file..."
else
    echo "  .env file already exists, skipping."
fi

# --- Generate APP_KEY if empty ---
if grep -q "APP_KEY=$" .env || [ "$(grep '^APP_KEY=' .env | cut -d= -f2)" = "" ]; then
    echo "[3/5] Generating APP_KEY..."
    # Run in docker temporarily to generate key
    docker run --rm -v "$(pwd):/var/www/html" php:8.4-cli-alpine \
        php /var/www/html/artisan key:generate --force
    echo "  APP_KEY generated!"
else
    echo "[3/5] APP_KEY already set, skipping."
fi

# --- Update APP_URL in .env to actual IP ---
CURRENT_IP=$(curl -s ifconfig.me 2>/dev/null || hostname -I 2>/dev/null | awk '{print $1}')
if grep -q "APP_URL=" .env; then
    sed -i "s|^APP_URL=.*|APP_URL=http://${CURRENT_IP}|" .env
fi

# --- Build and start containers ---
echo "[4/5] Building and starting containers..."
docker compose -f deploy/docker-compose.prod.yml --env-file .env up -d --build

echo ""
echo "[5/5] Deployment complete!"
echo "==================================="
echo ""
echo "  ✅ Your app is live at:"
echo "     http://${CURRENT_IP}"
echo ""
echo "  📧 Daily expense report will be sent at 23:59"
echo ""
echo "  ℹ️  To stop the app:"
echo "     docker compose -f deploy/docker-compose.prod.yml down"
echo ""
echo "  ℹ️  To view logs:"
echo "     docker compose -f deploy/docker-compose.prod.yml logs -f"
echo ""
