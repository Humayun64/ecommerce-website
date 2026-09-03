# Batch 4 — Attributes and variations

## What this adds

- **Attributes**: Size, Shade, Capacity — defined once, reused everywhere
- **Values** belong to an attribute: 15 ml, 30 ml, 50 ml
- **Variation matrix** on the product form: tick values, generate the grid
- **Automatic conversion** of your existing Batch 2 variants into Size values — nothing to re-enter
- Two attributes maximum per product, on purpose

## Install

```
xcopy "C:\Users\HUMAIYON KABIR\Downloads\batch-4\batch-4" "C:\Users\HUMAIYON KABIR\amjr_global" /E /I /Y
cd "C:\Users\HUMAIYON KABIR\amjr_global"
php artisan migrate
php artisan route:clear
php artisan view:clear
php artisan serve
```

Two migrations run: one creates the tables, the second converts every existing
variant label into a Size value and links it up.

## Test checklist

**Conversion worked**
- [ ] Admin → Attributes shows one attribute, "Size"
- [ ] Its values include 15 ml, 30 ml, 55 ml, 100 ml, 150 ml
- [ ] Products count against Size is 4

**Existing product still intact**
- [ ] Open Anua Niacinamide → Variations panel
- [ ] The Size axis is ticked, with 15 ml and 30 ml highlighted in gold
- [ ] Two rows below with the prices from the seeder
- [ ] Save without changing anything, reopen — still two rows, same prices

**Generating a grid**
- [ ] Attributes → Add attribute → name it "Shade", add values "Shade 21" and "Shade 23"
- [ ] Open any product → tick Size and Shade
- [ ] Tick 15 ml, 30 ml, Shade 21, Shade 23
- [ ] Click "Generate variations" → 4 rows appear, labelled "15 ml / Shade 21" and so on
- [ ] Fill in prices, save, reopen — all four survive

**Data is kept when regenerating**
- [ ] Type a price into one row
- [ ] Untick one value, click Generate again
- [ ] The remaining rows keep their prices — only removed combinations disappear

**The two-axis cap**
- [ ] Try ticking a third attribute — it refuses with a message

**Single-variation products**
- [ ] Open a product with no sizes, leave everything unticked
- [ ] The plain "Stock quantity" box is visible and works as before

## How it works

A variation's label is rebuilt from its values every time you save, so it can never
drift out of sync. Rename "30 ml" to "30ml" on the attribute and every variation
using it relabels itself.

Each variation is fingerprinted by its sorted value ids. That is how the matrix
knows which row is which when you regenerate, and why prices survive.

A value that is in use by a variation will not be deleted even if you remove its row
from the attribute form. Deleting it would leave variations pointing at nothing.

## Why two attributes maximum

Three axes with four values each is 64 variations. Nobody keeps 64 stock numbers
accurate. Two covers every realistic case in your catalog and keeps the grid usable.

## If something breaks

- `Class "App\Models\Attribute" not found` — `app/Models` did not copy
- Generate button does nothing — `public/js/product-form.js` did not copy
- Value chips do not highlight — `public/css/admin.css` did not copy
- `Table 'amjr_db.attributes' doesn't exist` — the migration did not run
