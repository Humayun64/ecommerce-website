# Batch 7 — Shopping cart

## What this adds

- Cart page at `/cart` with quantity changes and removal
- Add to cart from the product page, with the chosen size
- Quick-add on product cards for single-size products
- Live cart badge in the header
- **Guest carts that survive login** — add as a guest, log in, everything is still there
- Stock capping everywhere: you cannot put 12 in the cart when 4 are left
- Free-delivery nudge showing how much more is needed

## Install

```
xcopy "C:\Users\HUMAIYON KABIR\Downloads\batch-7\batch-7" "C:\Users\HUMAIYON KABIR\amjr_global" /E /I /Y
cd "C:\Users\HUMAIYON KABIR\amjr_global"
php artisan migrate
php artisan route:clear
php artisan view:clear
php artisan serve
```

## Test checklist

**Basics**
- [ ] Open a single-size product from a listing — the card has an Add to cart button
- [ ] Click it — a toast appears and the header badge goes to 1, without a page reload
- [ ] Click it twice more — badge reads 3
- [ ] Open /cart — one line, quantity 3, correct line total

**Variant products**
- [ ] Open the Anua Niacinamide — Add to cart is disabled until you pick a size
- [ ] Pick 15 ml, add — the cart line shows "15 ml" as a badge
- [ ] Go back, pick 30 ml, add — a second separate line, not a merge
- [ ] Both show their own price

**Stock capping**
- [ ] Find a product with 4 in stock, set the cart quantity to 99 and submit
- [ ] It caps at 4 and tells you why
- [ ] The + button greys out once you hit the limit
- [ ] In admin, drop that product's stock to 2, reload the cart — the amber warning appears

**Guest to logged-in merge (the important one)**
- [ ] Log out completely
- [ ] Add two different products as a guest
- [ ] Log in as any user
- [ ] Both items are still in the cart
- [ ] Log out, add one more as a guest, log back in — quantities combined, not duplicated

**Cart maths**
- [ ] Subtotal matches the lines
- [ ] Below ৳2,000 the delivery charge shows and a nudge says how much more for free delivery
- [ ] Above ৳2,000 it says Free

**Without JavaScript**
- [ ] Disable JS in the browser and add to cart — it still works, just with a page reload

## Notes

**Carts live in the database for guests too**, keyed by a token in the session. One
code path instead of two, which is why the merge on login is reliable rather than a
special case that breaks.

**Prices are read live, never stored on the cart.** Change a price in admin and open
carts pick it up on the next page load. Orders will take a snapshot instead, in
Batch 9 — the cart is a wish, the order is a contract.

**Add to cart posts a real form** and is intercepted by JavaScript for the no-reload
path. If fetch fails for any reason it falls back to submitting normally, so the cart
works even on a bad connection.

**Checkout is deliberately disabled.** Addresses, shipping zones, order placement and
cash on delivery are Batch 8.

## If something breaks

- `Route [cart.index] not defined` — run `php artisan route:clear`
- Badge never updates — `public/js/cart.js` did not copy
- `Undefined variable $cartCount` — `app/Providers/AppServiceProvider.php` did not copy
- 403 when changing quantity — you are editing a cart that is not yours; clear cookies
- `Table 'amjr_db.carts' doesn't exist` — the migration did not run
