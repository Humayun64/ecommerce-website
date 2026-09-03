# Batch 11 — Customer login, registration and dashboard

## What this adds

- **Login, register, forgot password, reset password** — all in the navy and gold design,
  replacing Breeze's default purple screens
- **Phone number on registration**, validated as a Bangladeshi mobile
- **Guest orders claimed automatically at signup** — register with the number you
  ordered with and your past orders are already there
- **Account overview** at `/account` with order count, total spent, orders on the way,
  recent orders and your default address
- **Profile page** rebuilt in the site design, with phone editing and password change
- Account sidebar with icons: Overview, My orders, Addresses, Track a parcel, Profile,
  and Admin panel for admin accounts

## Install

```
xcopy "C:\Users\HUMAIYON KABIR\Downloads\batch-11\batch-11" "C:\Users\HUMAIYON KABIR\amjr_global" /E /I /Y
cd "C:\Users\HUMAIYON KABIR\amjr_global"
php artisan route:clear
php artisan view:clear
php artisan serve
```

No migration — `phone` was added to users back in Batch 1.

## Test checklist

**Auth pages**
- [ ] /login — navy panel on the right, form on the left, no Laravel logo
- [ ] Wrong password — error appears in a red box at the top of the card
- [ ] /register — name, phone, email, password
- [ ] Enter `12345` as the phone — refused with a clear message
- [ ] Enter `+880 1347-419040` — accepted and stored as `01347419040`
- [ ] Forgot password page matches the design
- [ ] Resize narrow — the navy panel stacks below the form

**Signup claims guest orders (the good bit)**
- [ ] Log out, place a guest order using a phone number with no account
- [ ] Register with that exact number
- [ ] The welcome message says how many past orders were found
- [ ] They are already in My orders

**Dashboard**
- [ ] /account shows orders placed, total spent, and how many are on the way
- [ ] Recent orders list, three at most
- [ ] Default address shown, or a prompt to add one
- [ ] If you have unclaimed guest orders, an amber bar offers to add them

**Profile**
- [ ] Change your name and phone, save — "Saved." appears
- [ ] Change your password, log out, log back in with the new one
- [ ] The delete account box asks for your password

## Notes

**Registration claims guest orders immediately**, not just via the button. Most people
order once as a guest and sign up afterwards, so doing it at signup is the moment it
matters. The button on the orders page stays for anyone who adds their phone later.

**Phone is normalised identically in four places** — checkout, registration, profile
and address forms — so the same customer always matches themselves regardless of how
they type it.

**Breeze's `RegisteredUserController` is overwritten** to add the phone field and the
claim. If you ever re-run `php artisan breeze:install` it would be replaced, so do not.

**Profile lives at `/profile`** still, so Breeze's routes are untouched; only the view
and the form request changed.

## If something breaks

- Login page still purple — `resources/views/auth` did not copy, or run `php artisan view:clear`
- `Unable to locate a class or view for component [auth-shell]` — `resources/views/components` did not copy
- `Route [account.dashboard] not defined` — run `php artisan route:clear`
- Phone not saving on register — `app/Http/Controllers/Auth` did not copy
- Phone not saving on profile — `app/Http/Requests/ProfileUpdateRequest.php` did not copy
