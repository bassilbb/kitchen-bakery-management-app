# Kitchen & Bakery - How It Works

This document explains how the two departments work inside the app, how they
connect, and who can see what.

## Departments and permissions

Every user is either an **Admin** or a **Staff** member assigned to one department.

| User            | Role    | Department | Sees                                                                  |
|-----------------|---------|------------|-----------------------------------------------------------------------|
| Admin           | admin   | -          | Everything, including Reports and the Users page                      |
| Kitchen staff   | staff   | kitchen    | Dashboard, Sell (POS), Orders, Categories, Profile, **Ingredients**, **Suppliers** |
| Bakery staff    | staff   | bakery     | Dashboard, Sell (POS), Orders, Categories, Profile, **Products**, **Recipes**, **Baking / Production** |

- Reports and the Users (permissions) page are **admin only** - staff can never see them.
- The sidebar adapts automatically to what the logged-in user may access.
- Admins assign role + department on the **Users** page.

### Feature to department map

| Module                 | Kitchen | Bakery | Both | Admin only |
|------------------------|:-------:|:------:|:----:|:----------:|
| Dashboard              |         |        |  x   |            |
| Sell (POS)             |         |        |  x   |            |
| Orders / Refunds       |         |        |  x   |            |
| Customers              |         |        |  x   |            |
| Categories             |         |        |  x   |            |
| Profile                |         |        |  x   |            |
| Ingredients            |    x    |        |      |            |
| Suppliers              |    x    |        |      |            |
| Receive stock / purchases |  x    |        |      |            |
| Products               |         |    x   |      |            |
| Recipes (bill of materials) |    |    x   |      |            |
| Baking / Production    |         |    x   |      |            |
| Reports                |         |        |      |     x      |
| Expenses               |         |        |      |     x      |
| Users & permissions    |         |        |      |     x      |

## How the Kitchen side works

The kitchen owns **raw materials**: everything that comes into the shop before it
is baked or cooked.

1. **Suppliers** - Record your vendors (flour mill, dairy, chocolate importer)
   with their contact details.
2. **Ingredients** - Add every raw material (flour, sugar, butter, eggs, yeast,
   cocoa) with its unit (kg, L, piece), cost per unit, and a low-stock threshold.
3. **Receive Stock** - When a delivery arrives, record a purchase for an
   ingredient. The app adds the quantity to stock and updates the unit cost.
4. **Adjust Stock** - Correct counts for spillage, spoilage or miscounts with a
   +/- adjustment and a reason.
5. **Movement history** - Every purchase, usage and adjustment is logged, so the
   kitchen always knows where stock went.

The kitchen's job is to keep raw material levels healthy. When a product is
baked, the app automatically deducts the used ingredients - the kitchen can see
exactly what was consumed in the movement history of each ingredient.

## How the Bakery side works

The bakery owns **finished goods**: the products made from the raw materials.

1. **Products** - Add what you sell (baguette, croissant, chocolate cake) with a
   price, category, unit, and low-stock threshold.
2. **Recipes** - Define the bill of materials: how much of each ingredient goes
   into one unit of the product (e.g. 0.5 kg flour per sourdough loaf). The app
   recalculates the product's estimated cost automatically when you save.
3. **Baking / Production** - Start a batch by picking a product and a quantity.
   The app:
   - shows the required ingredients and whether you have enough,
   - deducts the ingredients from kitchen stock,
   - adds the finished units to product stock,
   - records a production batch with the production cost.
4. **Product stock** - Finished goods can be adjusted manually (waste, damaged,
   counted extra), and every sale deducts stock automatically.

## Where the two meet

The departments connect through **stock movements** and the **POS**:

- The bakery consumes ingredients (kitchen stock) when it produces goods.
- The kitchen replenishes those ingredients by receiving stock from suppliers.
- Products (bakery output) are sold at the **Sell (POS)** register, which deducts
  finished stock and creates orders that both departments can view.
- The dashboard and reports (admin) surface low stock on both sides so nobody
  runs out of flour or croissants.

```
  Supplier --> (Kitchen) Ingredient stock --recipe--> (Bakery) Product stock --> (POS) Sale
                          ^   |                                      |
                          |   +-- production usage -----------------+
                          +-- receive stock
```

## Customers, held sales and expenses

- **Customers** - Anyone can save customers (name, phone, email, address) and
  select them at POS checkout. Orders become linked to the customer, and the
  customer profile shows order history, total spent and average order. Customers
  are shared by both departments and can be exported to CSV.
- **Held sales (POS)** - A sale in progress can be put on hold, so the cashier
  can serve the next customer and resume the held cart later. Held carts check
  stock levels again before resuming.
- **Expenses** - Admins record business expenses (rent, utilities, wages,
  ingredients, packaging, ...). The dashboard shows today's expenses and net,
  and Reports show expenses for a date range, expenses by category, and net
  profit (sales minus expenses).

## Online payments (Paystack)

When an admin configures Paystack keys in **Settings**, the POS register shows
an "Online" payment option. Checking out with "Online":

1. Creates a `pending` order with a unique `transaction_reference` and redirects
   the customer to Paystack's hosted checkout page.
2. Stock is **not** deducted at this point, and the cart is kept so the sale can
   be retried if the customer does not pay.
3. Paystack redirects back to `/paystack/callback`. The app verifies the
   transaction with Paystack:
   - **Success** - order becomes `completed`, stock is deducted, a sale stock
     movement is logged, and the cart is cleared.
   - **Failure / abandoned** - order becomes `failed`, cart stays in place, and
     the cashier is shown an error message.
4. Orders paid online are marked with their status (pending / completed /
   failed) and the payment reference in the Orders list and receipts.
