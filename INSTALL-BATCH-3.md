# Batch 3 — Product management, images, SEO

## What this adds

- **Add and edit products** — full form with everything
- **Cost price** on products and each size, so margin and stock value are real numbers
- **Image upload** — multiple images, pick the main one, auto-resized to 1400px
- **Size management** — add and remove sizes inline, each with its own price, cost and stock
- **SEO panel** — meta title and description with a live Google preview and character counters
- **Facebook sharing fields** — controls the card people see when your link is pasted into a post
- **Dashboard upgrade** — stock value at cost, average margin, and a thin-margin warning list

## Install

**1. Copy the files in.** Extract, then in CMD:

```
xcopy "C:\Users\humay\Downloads\batch-3\batch-3" "C:\Users\humay\amjr_global" /E /I /Y
```

Check the path first with `dir` — if the files sit directly in `batch-3`, drop the second one.

Overwrites four files: `routes/web.php`, `app/Models/Product.php`,
`app/Models/ProductVariant.php`, `app/Http/Controllers/Admin/ProductController.php`,
`app/Http/Controllers/Admin/DashboardController.php`, `resources/views/admin/dashboard.blade.php`,
`resources/views/admin/layouts/app.blade.php` and `public/css/admin.css`. All expected.

**2. Run the migration:**

```
cd C:\Users\humay\amjr_global
php artisan migrate
```

**3. Link storage — this is required or images will not display:**

```
php artisan storage:link
```

Run this once. It creates `public/storage` pointing at `storage/app/public`.
If Windows refuses with a symlink permission error, open CMD **as Administrator**
and run it again.

**4. Clear caches and run:**

```
php artisan route:clear
php artisan view:clear
php artisan config:clear
php artisan serve
```

## Test checklist

**Adding a product**
- [ ] Products → Add product opens the form
- [ ] Fill name, SKU and price only, save — it works, everything else is optional
- [ ] It redirects to the edit page with a green confirmation

**Margin**
- [ ] On any product, type a price of 1000 and a cost of 700
- [ ] The panel header immediately shows "Profit ৳300 per unit — 30% margin" in green
- [ ] Change cost to 950 — it turns amber (5% margin)
- [ ] Change cost to 1200 — it turns red (negative)
- [ ] Save, then check the products list shows the margin badge

**Sizes**
- [ ] Click "Add a size", fill 30 ml at 1450, stock 8
- [ ] Add a second: 15 ml at 950, stock 12
- [ ] The "Stock quantity" box disappears and a note explains why
- [ ] Save, reopen — both sizes are still there
- [ ] Products list shows "from ৳950" and stock of 20
- [ ] Remove one size, save, reopen — it's gone

**Images**
- [ ] Upload two photos, save
- [ ] Both appear, the first is marked "Main"
- [ ] Click "Make main" on the second — the gold border moves
- [ ] Remove one — it disappears
- [ ] The products list now shows a thumbnail

**SEO**
- [ ] Type in the name field — the Google preview title updates as you type
- [ ] Type a meta title — the preview switches to it, counter goes green past 50
- [ ] Type past 70 characters — it stops you
- [ ] Save and reopen — values persisted

**Dashboard**
- [ ] Shows stock value at cost
- [ ] Warns how many products still have no cost price
- [ ] "Thin margins" lists anything under 15%

**Delete**
- [ ] Delete a test product — it vanishes from the list
- [ ] In phpMyAdmin, the row is still in `products` with `deleted_at` filled in

## Notes

**Deletes are soft.** The row stays in the database with a `deleted_at` timestamp so
old orders keep working once orders exist. It disappears from the admin and the
storefront immediately.

**Images are resized on upload** to 1400px on the long edge and saved as JPEG at 82%
quality, using PHP's GD extension. No composer package needed. Upload straight from
your phone; a 4 MB photo lands as roughly 200 KB.

**Cost price is never shown to customers.** It only drives the margin readout, the
products list, and the dashboard.

**The `og_image` column exists but has no field yet.** Facebook will fall back to your
main product image, which is the right behaviour. A separate share image only matters
if you want different artwork for social, which we can add later.

## If something breaks

- Images upload but show as broken — `php artisan storage:link` was not run
- `Class "App\Services\ImageService" not found` — `app/Services` did not copy
- `Class "App\Http\Requests\ProductRequest" not found` — `app/Http/Requests` did not copy
- Variant rows do not add — `public/js/product-form.js` did not copy
- Form looks unstyled — `public/css/admin.css` did not copy
- `Column not found: cost_price` — the migration did not run
