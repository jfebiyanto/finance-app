# Finance App - Setup Guide

## Prerequisites
- PHP 8.2+
- Composer
- MySQL 8.0+ (or MariaDB 10.3+)
- Node.js & NPM (for frontend assets)

## Database Setup

1. **Start your MySQL server** (XAMPP, Laragon, or standalone)

2. **Create the database:**
   ```sql
   CREATE DATABASE finance_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

3. **Update `.env`** if your MySQL credentials differ from the defaults:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=finance_app
   DB_USERNAME=root
   DB_PASSWORD=
   ```

## Installation

```bash
# Install PHP dependencies
composer install

# Install & build frontend assets
npm install
npm run build

# Run migrations and seeders
php artisan migrate:fresh --seed

# Start the development server
php artisan serve
```

Then visit `http://localhost:8000` and login with:
- **Email:** demo@finance.app
- **Password:** password

## Features

| Feature | Description |
|---------|-------------|
| **Dashboard** | Monthly overview with income/expense charts, budget progress, investment summary, recent transactions, and active targets |
| **Transactions** | Record daily expenses and income with category support |
| **Categories** | Manage expense, income, debt, and investment categories |
| **Debts** | Track debts with payment history and remaining balance |
| **Investments** | Monitor investment portfolio with profit/loss tracking |
| **Budgets** | Set monthly budgets per category with progress tracking |
| **Financial Targets** | Set and track financial goals (savings, debt payment, investment) |

## Default Categories

### Expenses
🍕 Food & Drinks, 🚗 Transportation, 🛍️ Shopping, 🎬 Entertainment, 💡 Utilities, 🏠 Rent, 🏥 Healthcare, 📚 Education

### Income
💰 Salary, 💻 Freelance, 🏪 Business, 🎁 Gifts

### Debt
🏦 Loan, 💳 Credit Card

### Investment
📈 Stocks, 🪙 Crypto, 🥇 Gold
