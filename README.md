# ⚡ Match Exchange Engine

A lightweight matching engine demonstrating financial integrity, asset locking, concurrency safety, and real-time UI synchronization.

## 📌 Overview

This project simulates a simplified cryptocurrency trading system consisting of:

- User authentication (login/logout)
- Wallet visibility (USD + per-asset balances)
- Limit order placement/cancellation
- Queue-driven matching engine
- Real-time settlement events delivered over Pusher
- Vue UI that updates instantly when trades execute

## Installation

### Backend Setup

1. Clone the repository
2. Navigate to the backend directory:
   ```bash
   cd backend
   ```
3. Install PHP dependencies:
   ```bash
   composer install
   ```
4. Copy the environment file:
   ```bash
   cp .env.example .env
   ```
5. Start Sail:
   ```bash
   ./vendor/bin/sail up -d
   ```
6. Generate application key:
   ```bash
   ./vendor/bin/sail artisan key:generate
   ```
7. Run migrations and seeders:
   ```bash
   ./vendor/bin/sail artisan migrate --seed
   ```
8. Start a queue worker in a separate terminal (⚠️ If this is not running, orders will be accepted but trades will never match):
   ```bash
   ./vendor/bin/sail artisan queue:work
   ```

### Frontend Setup

1. Navigate to the frontend directory:
   ```bash
   cd frontend
   ```
2. Install dependencies:
   ```bash
   npm install
   ```
3. Start the development server:
   ```bash
   npm run dev
   ```

## Usage

1. Access the application through your browser at `http://localhost:5173`
2. Log in using your credentials
3. View your wallet balance and asset holdings
4. Perform trading operations through the trading interface

## Technologies

- Frontend:
    - Vue.js 3
    - TypeScript
    - Tailwind CSS
    - Vue Router

- Backend:
    - Laravel 12
    - Laravel Sanctum for authentication
    - MySQL
    - Laravel Sail for Docker-based development
