# Batch 8 — Checkout, orders, cash on delivery

## What this adds

- Checkout page with guest checkout, no account required
- Shipping zones with real rates, and free delivery above your threshold
- Order placement inside a locked transaction — stock cannot go negative
- Order confirmation page with the order number
- Public order tracking at `/track`, needing both the order number and the phone
- Saved addresses for logged-in customers
- Cost snapshot on every line, so profit reporting survives future price changes

## Install

```
xcopy "C:\Users\HUMAIYON KABIR\Downloads\batch-8\batch-8" "C:\Users\HUMAIYON KABIR\amjr_global" /E /I /Y
cd "C:\Users\HUMAIYON KABIR\amjr_global"
php artisan migrate
php artisan db:seed --class=Database\Seeders\ShippingZoneSeeder
php artisan route:clear
php artisan view:clear
php artisan serve
```

## Test checklist

**Placing an order as a guest**
- [ ] Log out, add two products, go to cart, click Proceed to checkout
- [ ] Leave the phone empty and submit — it refuses
- [ ] Type `+880 1347-419040` — it is accepted and cleaned to `01347419040`
- [ ] Type `12345` — refused with a clear message
- [ ] Pick a delivery area — the charge and total update instantly
- [ ] Place the order — you land on the confirmation page with a number like `AMJR-260903-4817`

**Stock actually moved**
- [ ] Note a product's stock in admin before ordering
- [ ] Place an order for 2 of it
- [ ] Admin shows stock reduced by exactly 2

**Free delivery**
- [ ] Build a cart over ৳2,000 — delivery shows Free regardless of area
- [ ] Below it, the chosen area's rate applies

**Overselling is blocked**
- [ ] Put the last 3 of something in your cart but do not check out
- [ ] In admin, set that product's stock to 1
- [ ] Go back and place the order — it is refused, telling you only 1 is left
- [ ] Nothing was ordered and stock is untouched

**Tracking**
- [ ] Go to /track, enter the order number and the phone you used — the order appears with a progress bar
- [ ] Enter the right order number with a different phone — not found
- [ ] The confirmation URL opened in a private window gives 403

**Logged-in customer**
- [ ] Log in, check out with "Save this address" ticked
- [ ] Check out again — the form is pre-filled

## Notes

**The stock lock is the important part.** Placing an order opens a transaction,
takes a `SELECT … FOR UPDATE` on each product or variant row, re-reads the real
stock, and only then writes the order and decrements. If two customers buy the last
bottle at the same instant, the second waits for the lock, sees zero, and is refused.
Without this you sell things you do not have, and you find out when the courier calls.

**Order lines are snapshots.** Name, size label, SKU, brand, unit price and unit cost
are copied onto the order at purchase time. Rename a product or change its price next
month and old orders stay exactly as they were sold.

**Unit cost is copied too**, which is why order-level profit will be correct in
reporting even after supplier costs change.

**Guest orders have no user_id.** The phone number is the identity, which is why
tracking needs both the order number and the phone — an order number alone must not
expose someone's address.

**Phone numbers are normalised** before validation and storage, so the same customer
typing `+8801347419040` one day and `01347-419040` the next matches either way.

**Payment is cash on delivery only.** The online option is visible but disabled;
SSLCommerz comes later.

## What is not in this batch

Admin order management — the order list, status changes, invoices and courier
tracking numbers. That is Batch 9, and it is the other half of this. Right now orders
land in the database and you can see them in phpMyAdmin, but not yet in the admin panel.

## If something breaks

- `Route [checkout.show] not defined` — run `php artisan route:clear`
- `Table 'amjr_db.orders' doesn't exist` — the migration did not run
- No delivery areas on checkout — run the ShippingZoneSeeder line above
- Delivery total does not update — `public/js/checkout.js` did not copy
- 403 on the confirmation page — expected if you reopen it in another browser
