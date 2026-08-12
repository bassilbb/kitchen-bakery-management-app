# Kitchen & Bakery Management System

A simple, all-in-one Laravel application for running a kitchen and bakery business. Manage products, recipes, ingredient inventory, baking/production batches, point-of-sale checkout, orders, suppliers, and reports from one clean interface.

## Features

- **Dashboard** - Today's sales, order counts, weekly sales, today's expenses and net, low-stock alerts, top products, recent orders, today's payment breakdown, and interactive charts (7-day sales trend, sales vs expenses, and payment-method breakdown).
- **Sell (POS)** - Tap products to add them to a cart, adjust quantities, apply discounts, choose a payment method, hold a sale for later, and complete the sale with a printable receipt. Link the sale to a saved customer or type a new name.
- **Products** - Finished goods with categories, selling price, estimated cost, stock levels, and active/hidden status.
- **Recipes** - Define how many of each ingredient a product needs (bill of materials). Product cost is recalculated automatically when you save the recipe.
- **Ingredients** - Raw materials with stock levels, suppliers, cost per unit, stock value, and low-stock thresholds.
- **Receive Stock** - Record purchases to add ingredient stock and update unit cost, tied to a supplier.
- **Baking / Production** - The bakery files a production request for a batch (ingredients are calculated from the recipe), the kitchen approves and issues the ingredients (stock is deducted at issuance with a logged movement), then the bakery records the finished batch including wastage/rejected units. Fully audited.
- **Production Requests** - A formal request → review → issuance workflow between bakery and kitchen so raw materials only leave the store with approval and a stock movement record.
- **Orders** - Full order history with receipts, one-click refunds that restore stock, and CSV export.
- **Customers** - Save regular customers with contact details, pick them at checkout, and view each customer's order history, total spent, and average order.
- **Suppliers** - Manage supplier contact details.
- **Categories** - Organize products (bread, pastry, cake, etc.).
- **Expenses** - Record business expenses (rent, utilities, wages, ingredients, etc.) with categories. Reports show expenses in a date range, expenses by category, and net profit.
- **Reports** - Date-range sales totals, discounts, tax, expenses, net profit, payment-method breakdown, daily sales, top products, production history, most-used ingredients, inventory value, and CSV order export.
- **Stock Movements** - Complete audit trail of every purchase, usage, issuance to production, sale, and manual adjustment for ingredients and products.
- **Authentication** - Register, login, and profile/password management. The first registered user becomes the admin.
- **Role-based permissions** - Admins see everything. Cashiers handle all sales (Dashboard, Sell/POS, Orders, Customers, Profile). Kitchen staff see only Ingredients/Suppliers; Bakery staff see only Products/Recipes/Production/Categories. Reports, Expenses, and user management are admin-only. See `docs/KITCHEN_AND_BAKERY_OVERVIEW.md`.
- **Company settings** - Admins can upload the company logo and set the company name from Settings; they appear in the sidebar and on the login page.
- **Online payments (Paystack)** - Admins add their Paystack keys in Settings to let cashiers offer "Online" payment at the POS. The sale is created as a pending order and the customer is redirected to the Paystack checkout; stock is deducted only once Paystack confirms the payment, and a failed/abandoned payment leaves the cart intact to retry.

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
| Cashier           | All areas  | cashier@example.com   | password |
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

### Paystack (online payments)

To enable the "Online" payment option at the POS register:

1. Sign up at [Paystack](https://paystack.com) and open **Settings > API Keys**.
2. Log in to this app as an admin and go to **Settings**.
3. Paste your **Public key** (`YOUR_PAYSTACK_PUBLIC_KEY_HERE`) and **Secret key** (`YOUR_PAYSTACK_SECRET_KEY_HERE`) and save. The secret key is stored encrypted.
4. On the POS page, choose **Online** as the payment method to send the customer to Paystack's checkout page.

Notes:

- The order is created with a `pending` status while the customer is paying; it becomes `completed` only after Paystack verifies the payment, at which point stock is deducted.
- If the customer abandons or fails the payment, the order is marked `failed` and the cart stays in place so you can retry or switch to cash.
- The amount sent to Paystack is the order total in the smallest currency unit (kobo), so the total is always rounded to the nearest Naira.
- For local testing, Paystack also provides **test keys** and a test card mode.

## Tests

```bash
php artisan test
```

## Project Structure

- `app/Models/` - Eloquent models (Product, Ingredient, Order, Production, Supplier, Category, Customer, Expense, movements).
- `app/Http/Controllers/` - Auth, Dashboard, Pos, Product, Ingredient, Supplier, Category, Production, Order, Report, Customer, Expense, Profile controllers.
- `database/migrations/` - Schema for all tables.
- `database/seeders/DatabaseSeeder.php` - Demo users, products, recipes, suppliers, customers, expenses, production history, and sales.
- `resources/views/` - Blade + Tailwind views.

## Workflow Example

1. Add **ingredients** and record an opening stock or a purchase from a **supplier**.
2. Add **products** and define their **recipes** (ingredient quantities per unit).
3. The bakery files a **production request** for a batch; the kitchen approves it and **issues** the ingredients (stock is deducted and logged at issuance).
4. Once issued, the bakery records the **production batch** (units produced + wastage), adding finished goods to stock.
5. Use **Sell (POS)** to complete customer sales and print receipts.
6. Watch the **dashboard** for low stock and review everything in **Reports**.
