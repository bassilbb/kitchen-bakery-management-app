# Kitchen & Bakery - How It Works

This document explains how the two departments work inside the app, how they
connect, and who can see what.

## Departments and permissions

Every user is either an **Admin**, a **Cashier**, or a **Staff** member assigned to one department.

| User            | Role    | Department | Sees                                                                  |
|-----------------|---------|------------|-----------------------------------------------------------------------|
| Admin           | admin   | -          | Everything, including Reports and the Users page                      |
| Cashier         | cashier | -          | Dashboard, Sell (POS), Orders, Customers, Profile                     |
| Kitchen staff   | staff   | kitchen    | Dashboard, Profile, **Ingredients**, **Suppliers**                    |
| Bakery staff    | staff   | bakery     | Dashboard, Profile, **Products**, **Recipes**, **Baking / Production**, **Categories** |

- Reports, Expenses and the Users (permissions) page are **admin only**.
- Sales are handled **only by cashiers and admins** - kitchen and bakery staff
  never see the POS, Orders or Customers.
- Kitchen staff only see their ingredients/suppliers; bakery staff only see
  their products/recipes/production/categories; the two never see each other's
  inventory.
- The sidebar adapts automatically to what the logged-in user may access.
- Admins assign role + department on the **Users** page.

### Feature to department map

| Module                 | Kitchen | Bakery | Cashier | Admin only |
|------------------------|:-------:|:------:|:-------:|:----------:|
| Dashboard              |    x    |   x    |    x    |            |
| Profile                |    x    |   x    |    x    |            |
| Sell (POS)             |         |        |    x    |            |
| Orders / Refunds       |         |        |    x    |            |
| Customers              |         |        |    x    |            |
| Categories             |         |    x   |         |            |
| Production Requests    |    x    |    x   |         |            |
| Ingredients            |    x    |        |         |            |
| Suppliers              |    x    |        |         |            |
| Receive stock / purchases |  x    |        |         |            |
| Products               |         |    x   |         |            |
| Recipes (bill of materials) |    |    x   |         |            |
| Baking / Production    |         |    x   |         |            |
| Reports                |         |        |         |     x      |
| Expenses               |         |        |         |     x      |
| Users & permissions    |         |        |         |     x      |
| Settings               |         |        |         |     x      |

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
3. **Production Requests** - To bake, the bakery no longer deducts kitchen stock
   directly. Instead they file an **ingredient request** for the batch:
   - pick the product and quantity; the app calculates the required ingredients
     from the recipe and shows what is available,
   - a request cannot exceed available stock unless an admin approves an
     exception,
   - the request is submitted for the kitchen to review.
4. **Baking / Production** - Once the kitchen has issued the ingredients, the
   bakery records the finished batch: units produced and any wastage/rejected
   units. Finished units are added to product stock.
5. **Product stock** - Finished goods can be adjusted manually (waste, damaged,
   counted extra), and every sale deducts stock automatically.

## The ingredient request workflow

This is how the two departments move raw materials from the kitchen to the bakery:

1. **Bakery plans a batch** - creates a request (saved as a draft or submitted
   straight away). The system shows the required ingredients and marks any that
   are short. Stock is **not** deducted here.
2. **Kitchen reviews** - a submitted request is approved or rejected (with an
   optional reason). Kitchen staff decide before anything leaves the store.
3. **Kitchen issues** - on an approved request, the kitchen confirms the amounts
   actually handed over. Stock is **deducted at issuance**, an `issue` stock
   movement is logged against the request number, and the request becomes
   *Issued* (or *Partially Issued* if only part of a line was handed over).
4. **Bakery produces** - once fully issued, the bakery records the finished
   batch (units produced + wastage), which adds finished goods to product stock
   and closes the request as *Completed*.

Statuses: `draft → submitted → approved → (partially issued) → issued → completed`,
with `rejected` and `cancelled` as alternatives.

Because every issuance is a logged stock movement tied to the request number, you
can always answer: who requested it, who issued it, when, how much, and which
production batch consumed it.

## Where the two meet

The departments connect through **stock movements**, the **POS**, and the
**ingredient request workflow**:

- The bakery receives ingredients from kitchen stock through approved, issued
  production requests (never by directly editing kitchen stock).
- The kitchen replenishes those ingredients by receiving stock from suppliers.
- Products (bakery output) are sold by cashiers at the **Sell (POS)** register,
  which deducts finished stock and creates orders.
- The dashboard surfaces low stock per department, and reports (admin) show the
  financial picture, so nobody runs out of flour or croissants.

```
  Supplier --> (Kitchen) Ingredient stock --issue/request--> (Bakery) Product stock --> (POS) Sale
                          ^   |                                                 |
                          |   +-- production usage ----------------------------+
                          +-- receive stock
```

## Customers, held sales and expenses

- **Customers** - Cashiers save customers (name, phone, email, address) and
  select them at POS checkout. Orders become linked to the customer, and the
  customer profile shows order history, total spent and average order. Customers
  can be exported to CSV.
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
