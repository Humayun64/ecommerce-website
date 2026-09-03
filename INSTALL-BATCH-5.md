# Batch 5 — Store settings + storefront

## What this adds

- **Settings** table with an admin page: name, logo, favicon, contact, WhatsApp,
  homepage wording, delivery charges, social links, homepage SEO
- **The storefront** — header, nav, footer and homepage, in the approved navy and gold,
  driven entirely by your real catalog and settings
- Every UI string wrapped in `__()` so Bangla can be added later as one JSON file

## Install

```
xcopy "C:\Users\HUMAIYON KABIR\Downloads\batch-5\batch-5" "C:\Users\HUMAIYON KABIR\amjr_global" /E /I /Y
cd "C:\Users\HUMAIYON KABIR\amjr_global"
php artisan migrate
php artisan db:seed --class=Database\Seeders\SettingSeeder
php artisan route:clear
php artisan view:clear
php artisan config:clear
php artisan cache:clear
php artisan serve
```

`cache:clear` matters — settings are cached, and a stale empty cache shows blank text.

Overwrites `routes/web.php`, `app/Providers/AppServiceProvider.php`,
`database/seeders/DatabaseSeeder.php` and the admin layout. All expected.

## Test checklist

**Storefront**
- [ ] Open http://127.0.0.1:8000 — the homepage loads in navy and gold
- [ ] Nav shows your real categories: Cleansers, Toners, Serums, Sunscreen, Torches…
- [ ] Best sellers shows the products you marked Featured
- [ ] Just landed shows the six most recent
- [ ] Products with sizes show "from ৳950"
- [ ] Products with photos show them; the rest show the bottle placeholder
- [ ] Out-of-stock products show a greyed-out "Sold out" button
- [ ] Footer shows your address, phone and email
- [ ] Resize the browser narrow — it stays usable

**Settings**
- [ ] Admin → Settings loads
- [ ] Change the top bar message, save, reload the storefront — it changed immediately
- [ ] Upload a logo — it replaces the drawn mark in the header
- [ ] Change a delivery charge — the table at the bottom of the homepage updates
- [ ] Clear the Instagram URL — that footer link disappears

**Featured products**
- [ ] Admin → Products → edit one → tick "Feature on the homepage" → save
- [ ] It appears in Best sellers

## Notes

**Settings are cached forever** and the cache is cleared automatically on save. You
should never need `php artisan cache:clear` after the first install.

**Delivery charges here are display-only.** Real checkout calculation comes in Batch 9
with proper shipping zones. Keep the two in step until then.

**Category and product links go nowhere yet** — every `href` is `#`. Category listing,
search results and the product detail page are Batch 6. The homepage is deliberately
first so you can judge the design against real data before more pages are built on it.

**The logo is never re-encoded.** Product photos get converted to JPEG, which would
turn a transparent PNG logo black, so branding uploads are stored exactly as given.

## If something breaks

- Homepage is unstyled — `public/css/site.css` did not copy
- `Undefined variable $settings` — `app/Providers/AppServiceProvider.php` did not copy
- `Undefined variable $navCategories` — same file
- Text is blank everywhere — run `php artisan cache:clear`
- `Table 'amjr_db.settings' doesn't exist` — the migration did not run
- Logo uploads but does not show — run `php artisan storage:link`
