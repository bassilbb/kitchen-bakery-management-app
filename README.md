# Kitchen & Bakery Management System

A simple, all-in-one Laravel application for running a kitchen and bakery business. Manage products, recipes, ingredient inventory, baking/production batches, point-of-sale checkout, orders, suppliers, and reports from one clean interface.

## Features

- **Dashboard** - Today's sales, order counts, weekly sales, low-stock alerts, top products, recent orders, and a 7-day sales chart.
- **Sell (POS)** - Tap products to add them to a cart, adjust quantities, apply discounts, choose a payment method, and complete the sale with a printable receipt.
- **Products** - Finished goods with categories, selling price, estimated cost, stock levels, and active/hidden status.
- **Recipes** - Define how many of each ingredient a product needs (bill of materials). Product cost is recalculated automatically when you save the recipe.
- **Ingredients** - Raw materials with stock levels, suppliers, cost per unit, stock value, and low-stock thresholds.
- **Receive Stock** - Record purchases to add ingredient stock and update unit cost, tied to a supplier.
- **Baking / Production** - Produce a batch from a product recipe. The system checks ingredient availability, deducts ingredients, and adds finished stock. Fully logged.
- **Orders** - Full order history with receipts and one-click refunds that restore stock.
- **Suppliers** - Manage supplier contact details.
- **Categories** - Organize products (bread, pastry, cake, etc.).
- **Reports** - Date-range sales totals, discounts, tax, daily sales, top products, production history, most-used ingredients, and inventory value.
- **Stock Movements** - Complete audit trail of every purchase, usage, sale, and manual adjustment for ingredients and products.
- **Authentication** - Register, login, and profile/password management. The first registered user becomes the admin.
- **Role-based permissions** - Admins see everything. Kitchen staff see only Ingredients/Suppliers; Bakery staff see only Products/Recipes/Production. Reports and user management are admin-only. See `docs/KITCHEN_AND_BAKERY_OVERVIEW.md`.
- **Company settings** - Admins can upload the company logo and set the company name from Settings; they appear in the sidebar and on the login page.

## Requirements

- PHP 8.2+
- Composer 2
- Node.js 18+ (for building assets)
- SQLite (default) or MySQL/PostgreSQL

## Installation

```bash
# Install PHP dependencies
composer install

# Install and build frontend assets
npm install
npm run build

# Configure environment
cp .env.example .env
php artisan key:generate

# Create the database and run migrations with demo data
touch database/database.sqlite
php artisan migrate --seed

# Start the development server
php artisan serve
```

Then open `http://localhost:8000`.

### Demo accounts

| Role              | Department | Email                 | Password |
|-------------------|------------|-----------------------|----------|
| Admin             | All areas  | admin@example.com     | password |
| Staff (Bakery)    | Bakery     | staff@example.com     | password |
| Staff (Kitchen)   | Kitchen    | kitchen@example.com   | password |

## Configuration

Set the tax rate applied at checkout in your `.env`:

```
POS_TAX_RATE=0
```

You can also change the currency symbol (default Naira `₦`):

```
POS_CURRENCY=$
```

## Tests

```bash
php artisan test
```

## Project Structure

- `app/Models/` - Eloquent models (Product, Ingredient, Order, Production, Supplier, Category, movements).
- `app/Http/Controllers/` - Auth, Dashboard, Pos, Product, Ingredient, Supplier, Category, Production, Order, Report, Profile controllers.
- `database/migrations/` - Schema for all tables.
- `database/seeders/DatabaseSeeder.php` - Demo users, products, recipes, suppliers, production history, and sales.
- `resources/views/` - Blade + Tailwind views.

## Workflow Example

1. Add **ingredients** and record an opening stock or a purchase from a **supplier**.
2. Add **products** and define their **recipes** (ingredient quantities per unit).
3. Create a **production batch** to bake products - ingredients are deducted automatically.
4. Use **Sell (POS)** to complete customer sales and print receipts.
5. Watch the **dashboard** for low stock and review everything in **Reports**.
