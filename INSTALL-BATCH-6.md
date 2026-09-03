# Batch 6 — Category pages, search, product detail

## What this adds

- **/shop** — every product, with filters and sorting
- **Category pages** — a parent category shows everything beneath it
- **Brand pages**
- **Search** — the header box now works, matching name, description, SKU and brand
- **Filters** — price range, brand, size (any filterable attribute), in-stock only
- **Sorting** — newest, price up, price down, name
- **Product detail page** — image gallery, working size picker, live price and stock,
  quantity stepper, WhatsApp order link, related products
- **Product schema markup** and per-product Open Graph tags, using the SEO fields from Batch 3

## Install

```
xcopy "C:\Users\HUMAIYON KABIR\Downloads\batch-6\batch-6" "C:\Users\HUMAIYON KABIR\amjr_global" /E /I /Y
cd "C:\Users\HUMAIYON KABIR\amjr_global"
php artisan route:clear
php artisan view:clear
php artisan serve
```

No migration this time — nothing changed in the database.

## Test checklist

**Navigation**
- [ ] Homepage category tiles now open real category pages
- [ ] Nav links work; the current category is underlined in gold
- [ ] Brand strip on the homepage opens brand pages
- [ ] Clicking any product card opens its detail page

**Listing and filters**
- [ ] /shop shows all active products
- [ ] Open the Serums category — only serums
- [ ] Open Skincare (parent) — everything under it, all five sub-categories
- [ ] Tick a brand, Apply — the list narrows, the URL keeps the filter
- [ ] Set price 500 to 1000 — only products in range
- [ ] Tick a size value — only products stocking that size
- [ ] Tick "In stock only" — sold-out products disappear
- [ ] Sort by price low to high — variant products sort on their cheapest size
- [ ] "Clear all filters" resets
- [ ] Filter to something impossible — you get the empty state, not a blank page

**Search**
- [ ] Search "centella" — the two SKIN1004 products
- [ ] Search "anua" — matches by brand name
- [ ] Search "xyzabc" — the empty state

**Product page**
- [ ] Open the Anua Niacinamide
- [ ] Two size buttons appear under the price
- [ ] Click 15 ml — price changes to ৳950, SKU changes, stock line updates
- [ ] Click 30 ml — price changes to ৳1,450
- [ ] Before choosing, the button says the stock is unknown and Add to cart is disabled
- [ ] The quantity stepper will not go above the chosen size's stock
- [ ] Upload two images to a product, reopen — thumbnails appear, clicking swaps the main image
- [ ] The WhatsApp button opens a message pre-filled with the product name
- [ ] Related products appear at the bottom

**SEO**
- [ ] View source on a product page — `application/ld+json` block with the price and availability
- [ ] `og:title` and `og:image` present
- [ ] Untick "Allow search engines to index" in admin, reload — `noindex` appears

## Notes

**Impossible combinations are struck through.** On a product with two axes, choosing a
shade greys out any size you never stocked in that shade, rather than letting someone
pick a pair that does not exist and hitting an error at checkout.

**Add to cart does not work yet** — the cart is Batch 7. The button is deliberately
present and correctly enabled or disabled so the layout and stock logic can be tested now.

**Sorting by price uses the cheapest variant**, via a subquery, so a product whose 15 ml
is ৳950 sorts at 950 rather than at whatever sits in the product's own price column.

## If something breaks

- `Route [shop.product] not defined` — `routes/web.php` did not copy, or run `php artisan route:clear`
- Size buttons do nothing — `public/js/product-page.js` did not copy
- Page loads unstyled — `public/css/site.css` did not copy
- `Undefined variable $navCategories` — Batch 5's AppServiceProvider is missing
- 404 on a product — it is set to hidden in admin
