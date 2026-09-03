# Batch 2 — Catalog database + admin panel

## What this adds

- Five tables: `categories`, `brands`, `products`, `product_variants`, `product_images`
- Five Eloquent models with relationships and stock accessors
- Your 16 products seeded (12 skincare + 4 gadgets), with size variants
- `AdminMiddleware` protecting `/admin` behind the `is_admin` flag
- Admin panel in the navy and gold design: dashboard, category CRUD, brand CRUD, product listing

## Install

**1. Extract and copy.** Extract this folder, then from a CMD window:

```
xcopy "C:\Users\humay\Downloads\batch-2" "C:\Users\humay\amjr_global" /E /I /Y
```

Adjust the source path to wherever you extracted it.

This overwrites three existing files: `bootstrap/app.php`, `routes/web.php`, and
`database/seeders/DatabaseSeeder.php`. That is expected.

**2. Migrate and seed:**

```
cd C:\Users\humay\amjr_global
php artisan migrate
php artisan db:seed
```

If `php artisan migrate` reports nothing to do, the migration files did not land —
check that `database/migrations` now contains five files dated `2026_09_03_1000xx`.

**3. Clear caches:**

```
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

**4. Run it:**

```
php artisan serve
```

## Test checklist

Log in as `admin@amjrglobal.com` / `admin1234`, then go to
**http://127.0.0.1:8000/admin**

- [ ] Dashboard shows 16 products, 10 categories, 6 brands
- [ ] "Needs attention" card shows a number above zero
- [ ] "Running low" table lists Rice Cream and LED Rechargeable Torch
- [ ] Categories page shows Skincare and Gadgets with indented children
- [ ] Create a test category, then edit it, then delete it
- [ ] Deleting Skincare is refused because it has sub-categories
- [ ] Brands page shows product counts against each brand
- [ ] Deleting Anua is refused because it has products
- [ ] Products page lists all 16, "from ৳950" on the Anua niacinamide row
- [ ] Search for "centella" returns 2 products
- [ ] Filter by brand SKIN1004 returns 2 products
- [ ] Log out, log in as a non-admin user, visit /admin — you get a 403

## Notes

**The admin CSS is plain CSS**, at `public/css/admin.css`. It does not go through
Vite, so you never need `npm run build` after changing admin styling — just refresh.

**Prices and stock in the seeder are placeholders.** Real figures go in via the
admin panel once product editing lands in Batch 3.

**Product create/edit is intentionally not in this batch.** The listing is
read-only so you can confirm the data landed. Full product management, image
upload and variant editing is Batch 3.

## If something breaks

- `Class "App\Http\Middleware\AdminMiddleware" not found` — `bootstrap/app.php` did not copy
- `Route [admin.dashboard] not defined` — `routes/web.php` did not copy, or run `php artisan route:clear`
- `Table 'amjr_db.categories' doesn't exist` — migrations did not copy
- Admin page loads with no styling — `public/css/admin.css` did not copy
- Any other error, send me the full message
