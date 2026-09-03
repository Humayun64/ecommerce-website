# Batch 9 — Admin order management

## What this adds

- **Order list** with status tabs, search by number, name or phone, and date filters
- **Order detail** — items, totals, per-order profit, customer, courier and internal notes
- **Status flow** with timestamps, and automatic restocking when an order is cancelled
- **Printable invoice** with a cash-on-delivery collection line
- **Manual order entry** for orders that arrive by Facebook message or phone
- **Real sales dashboard** — revenue, gross profit, 14-day chart, top earners by profit

## Install

```
xcopy "C:\Users\HUMAIYON KABIR\Downloads\batch-9\batch-9" "C:\Users\HUMAIYON KABIR\amjr_global" /E /I /Y
cd "C:\Users\HUMAIYON KABIR\amjr_global"
php artisan route:clear
php artisan view:clear
php artisan serve
```

No migration — Batch 8 already created every table this needs.

## Test checklist

**Order list**
- [ ] Admin → Orders shows the orders you placed while testing Batch 8
- [ ] Status tabs across the top show counts
- [ ] Search by phone number finds the order
- [ ] Click an order — the detail page opens

**Status flow**
- [ ] Change a pending order to Confirmed — a timestamp appears in the sidebar
- [ ] Change it to Shipped, then Delivered
- [ ] On Delivered, payment flips to "Collected" automatically
- [ ] Note a product's stock, then cancel that order — stock goes back up by exactly the quantity ordered
- [ ] The message tells you the stock was returned

**Invoice**
- [ ] Click Invoice — opens in a new tab, print-ready
- [ ] It says how much cash the courier should collect
- [ ] Ctrl+P — the Print button disappears from the printed page

**Manual order (the one for your Facebook messages)**
- [ ] Orders → New order by hand
- [ ] Pick a product — the price fills in automatically and the quantity caps at stock
- [ ] Override the price to something lower, as you would when agreeing a discount
- [ ] Add a second line, fill in the customer, choose a zone, create
- [ ] It lands in the order list exactly like a storefront order
- [ ] Stock came off — check the product in admin
- [ ] Out-of-stock products cannot be selected

**Dashboard**
- [ ] Revenue and gross profit for this month
- [ ] The 14-day bar chart shows your test orders
- [ ] "Top earners" ranks by profit
- [ ] "Needs you today" counts orders to confirm and to ship

## Notes

**Cancelling restocks automatically**, and reopening a cancelled order warns you that
stock was already returned rather than silently taking it twice. Automatic re-decrement
on reopen would be worse: by then the stock has usually been sold to someone else.

**Manual orders use exactly the same locking path as the storefront.** Same
transaction, same row locks, same cost snapshot. That is deliberate — a second,
looser code path for admin orders is how stock numbers quietly drift apart.

**Top earners ranks by profit, not units.** The cheap item you sell forty of can easily
make less than the serum you sell six of, and the profit list is the one that should
drive what you reorder.

**Marking Delivered flips payment to collected** for cash-on-delivery orders, since
that is exactly what delivery means for COD.

## What is left

- Order confirmation emails and SMS
- Customer order history in their account area
- Coupons
- SSLCommerz online payment
- Hostinger deployment

## If something breaks

- `Route [admin.orders.index] not defined` — run `php artisan route:clear`
- Dashboard errors on `cost_total` — Batch 8's migration did not run
- Manual order lines do not appear — `public/js/manual-order.js` did not copy
- Status badges are grey — `public/css/admin.css` did not copy
