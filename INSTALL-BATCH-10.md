# Batch 10 — Customer accounts and order emails

## What this adds

- **Account area** at `/account` — order history, order detail with progress bar, saved addresses
- **Claim guest orders** — a customer who ordered as a guest then signed up can pull
  those orders into their account by matching phone number
- **Order confirmation email** with the full order and a tracking link
- **Status emails** on confirmed, shipped, delivered and cancelled
- Header "Account" now goes to the account area instead of Breeze's placeholder dashboard

## Install

```
xcopy "C:\Users\HUMAIYON KABIR\Downloads\batch-10\batch-10" "C:\Users\HUMAIYON KABIR\amjr_global" /E /I /Y
cd "C:\Users\HUMAIYON KABIR\amjr_global"
php artisan route:clear
php artisan view:clear
php artisan serve
```

No migration — Batch 8's tables cover everything here.

## Seeing the emails on XAMPP

XAMPP has no mail server, so real sending will fail locally. Set this in `.env`:

```
MAIL_MAILER=log
MAIL_FROM_ADDRESS=orders@amjrglobal.com
MAIL_FROM_NAME="AMJR Global"
```

Then `php artisan config:clear`. Emails are written to
`storage/logs/laravel.log` instead of being sent. Open that file after placing an
order and you will see the full message.

On Hostinger later, swap to real SMTP:

```
MAIL_MAILER=smtp
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=465
MAIL_USERNAME=orders@amjrglobal.com
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=ssl
```

## Test checklist

**Account area**
- [ ] Log in — the header Account link goes to /account
- [ ] Orders you placed while logged in are listed
- [ ] Click one — progress bar, items, totals, delivery address
- [ ] Addresses page: add one, it appears; add a second as default, the first loses the tag
- [ ] Remove an address

**Claiming guest orders**
- [ ] Log out, place an order as a guest using the phone number on your account
- [ ] Log back in, click "Find my guest orders"
- [ ] It reports how many were linked and they appear in your list
- [ ] Click it again — nothing new to claim

**Emails**
- [ ] With `MAIL_MAILER=log`, place an order with an email address filled in
- [ ] Open `storage/logs/laravel.log` — the confirmation email is at the bottom
- [ ] In admin, change that order to Shipped
- [ ] The log now has a second email about the parcel being on its way
- [ ] Place an order with the email field left empty — no email, no error

## Notes

**Email failure can never break an order.** Every send is wrapped in a try/catch that
logs and moves on. The order is already committed by the time we try to send — a mail
server being down must not lose you a sale.

**Emails are sent after the transaction commits**, never inside it. Sending inside a
transaction risks emailing a customer about an order that then rolls back.

**Claiming is matched on phone number only**, and only picks up orders with no owner.
An order already attached to another account is never moved.

**Emails use table-based HTML** with inline styles. It looks dated, but Gmail and
Outlook still strip modern CSS, and an email that renders is worth more than one that
validates.

## If something breaks

- `Route [account.orders] not defined` — run `php artisan route:clear`
- Emails do not appear in the log — check `MAIL_MAILER=log` and run `php artisan config:clear`
- `View [emails._layout] not found` — `resources/views/emails` did not copy
- Account page unstyled — `public/css/site.css` did not copy
