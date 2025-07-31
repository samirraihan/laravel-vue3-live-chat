#!/bin/bash

# Live Chat Application Setup Script
echo "🚀 Setting up Live Chat Application..."

# Check if required tools are installed
echo "📋 Checking prerequisites..."

# Check PHP
if ! command -v php &> /dev/null; then
    echo "❌ PHP is not installed. Please install PHP 8.1 or higher."
    exit 1
fi

# Check Composer
if ! command -v composer &> /dev/null; then
    echo "❌ Composer is not installed. Please install Composer."
    exit 1
fi

# Check Node.js
if ! command -v node &> /dev/null; then
    echo "❌ Node.js is not installed. Please install Node.js 18 or higher."
    exit 1
fi

# Check npm
if ! command -v npm &> /dev/null; then
    echo "❌ npm is not installed. Please install npm."
    exit 1
fi

echo "✅ All prerequisites are installed!"

# Install PHP dependencies
echo "📦 Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader

# Install Node.js dependencies
echo "📦 Installing Node.js dependencies..."
npm install

# Setup environment
echo "⚙️ Setting up environment..."
if [ ! -f .env ]; then
    cp .env.example .env
    echo "📝 Created .env file"
fi

# Generate application key
php artisan key:generate

# Setup database
echo "🗄️ Setting up database..."
if [ ! -f database/database.sqlite ]; then
    touch database/database.sqlite
    echo "📝 Created SQLite database file"
fi

# Run migrations
echo "🔄 Running database migrations..."
php artisan migrate --force

# Seed demo data
echo "🌱 Seeding demo data..."
php artisan db:seed --class=ChatSeeder --force

# Build frontend assets
echo "🏗️ Building frontend assets..."
npm run build

echo ""
echo "🎉 Setup completed successfully!"
echo ""
echo "📋 To start the application:"
echo "   1. Start Laravel server:    php artisan serve --host=0.0.0.0 --port=8000"
echo "   2. Start Socket.IO server:  npm run socket"
echo "   3. Open browser:            http://localhost:8000"
echo ""
echo "💡 For development mode:"
echo "   1. Start Laravel server:    php artisan serve"
echo "   2. Start Vite dev server:   npm run dev"
echo "   3. Start Socket.IO server:  npm run socket"
echo ""
echo "📚 Check README.md for detailed documentation."
echo ""
echo "Happy chatting! 💬✨"

