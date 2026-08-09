# 🏔️ TravelAI Nepal

[![Laravel](https://img.shields.io/badge/Laravel-13-red?style=flat&logo=laravel)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.3-blue?style=flat&logo=php)](https://php.net)
[![TailwindCSS](https://img.shields.io/badge/Tailwind-4.0-38B2AC?style=flat&logo=tailwind-css)](https://tailwindcss.com)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

**Nepal's Unified Digital Tourism Ecosystem** — Connecting travelers, tourism businesses, and professionals through AI-powered planning, seamless booking, QR check‑ins, and emergency safety features.

---

## 📖 Documentation

**👉 [Full Master Product, Architecture, Database & Implementation Blueprint](docs/master_planning_and_all.md)**

This is the **Single Source of Truth** for the entire project — containing:
- ✅ Complete system audit
- 🏗️ Target architecture (User → Provider → Service → Booking)
- 📊 Database design & migration strategy
- 🗺️ Phased implementation roadmap (12 phases)
- 🔒 Security & multi‑tenancy strategy
- 🧪 Testing & rollback plans

---

## 🚀 Quick Start

### Prerequisites
- PHP 8.3+
- Composer
- MySQL 5.7+
- Node.js 18+ & NPM

### Installation

```bash
# Clone the repository
git clone https://github.com/your-username/TravelAI-Nepal.git
cd TravelAI-Nepal

# Install PHP dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure your database in .env
# DB_DATABASE=travelai_db
# DB_USERNAME=root
# DB_PASSWORD=

# Run migrations
php artisan migrate

# Create storage link for images
php artisan storage:link

# Install frontend dependencies
npm install

# Build assets (production)
npm run build

# Or for development with hot reload
npm run dev