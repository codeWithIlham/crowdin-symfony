#!/bin/bash
set -e

cd /app

# If bin/console doesn't exist (Symfony not set up), create it
if [ ! -f "bin/console" ]; then
    echo "Creating Symfony 6.4 project..."

    # Create in temp directory first
    mkdir -p /tmp/symfony
    composer create-project symfony/skeleton /tmp/symfony "6.4.*" --no-interaction
    
    # Move files (save our config first)
    mkdir -p /tmp/config
    cp -r *.md /tmp/config/ 2>/dev/null || true
    cp .env /tmp/config/ 2>/dev/null || true
    cp .gitignore /tmp/config/ 2>/dev/null || true
    cp docker-compose.yml Dockerfile entrypoint.sh init.sh /tmp/config/ 2>/dev/null || true
    
    # Clean /app
    rm -rf /app/*
    rm -rf /app/.* 2>/dev/null || true
    
    # Copy Symfony
    cp -r /tmp/symfony/* /app/
    cp -r /tmp/symfony/.* /app/ 2>/dev/null || true
    
    # Restore our config
    cp -r /tmp/config/* /app/ 2>/dev/null || true
    
    rm -rf /tmp/symfony /tmp/config

    echo "Installing required packages..."
    composer require \
        symfony/orm-pack \
        symfony/form \
        symfony/validator \
        symfony/security-bundle \
        symfony/twig-bundle \
        --no-interaction
else
    echo "Symfony exists, installing dependencies..."
    composer install --no-interaction
fi

# Wait for MySQL
echo "Waiting for MySQL..."
for i in {1..30}; do
    if nc -z mysql 3306 2>/dev/null; then
        echo "MySQL ready!"
        break
    fi
    echo "Attempt $i/30: Waiting for MySQL..."
    sleep 2
done

# Setup database
echo "Setting up database..."
php bin/console doctrine:database:create --if-not-exists 2>/dev/null || true
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration 2>/dev/null || true

echo "Setup complete! Starting server on 0.0.0.0:8000..."
exec php -S 0.0.0.0:8000 -t public
