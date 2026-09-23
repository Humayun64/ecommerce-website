<?php

namespace App\Support;

use App\Models\Setting;

/**
 * Starter content for the quick-create buttons.
 *
 * Written for a Bangladeshi cash-on-delivery shop and filled in from the
 * store settings, so what lands is close to publishable rather than a
 * page of placeholder text nobody ever edits.
 */
class PageTemplates
{
    public static function all(): array
    {
        return [
            'about-us'        => 'About us',
            'return-policy'   => 'Return & refund policy',
            'shipping-policy' => 'Shipping policy',
            'privacy-policy'  => 'Privacy policy',
            'terms'           => 'Terms of service',
            'contact'         => 'Contact us',
        ];
    }

    public static function get(string $key): ?array
    {
        $name    = Setting::get('store_name', 'AMJR Global');
        $phone   = Setting::get('store_phone', '');
        $email   = Setting::get('store_email', '');
        $address = Setting::get('store_address', '');
        $days    = (int) Setting::get('return_days', 7);
        $free    = (float) Setting::get('free_delivery_over', 0);

        $templates = [
            'about-us' => [
                'title'   => 'About us',
                'excerpt' => "Who we are and why every product we sell is the real thing.",
                'content' => <<<TXT
                ## Who we are

                {$name} imports Korean skincare and everyday gadgets into Bangladesh and sells them directly, without a middleman markup.

                ## Why we started

                Counterfeit skincare is everywhere here, and it usually looks perfect. People pay real money for a bottle that does nothing, and there is no way to tell until weeks later. We started because we were tired of it.

                ## How we keep it honest

                - Every product is imported in our own name, not bought from a local reseller
                - Boxes arrive sealed, with the batch code intact, so you can check it against the brand yourself
                - We tell you when something is out of stock instead of shipping you a substitute

                ## Where to find us

                {$address}

                Call or WhatsApp us on {$phone} — we answer.
                TXT,
            ],

            'return-policy' => [
                'title'   => 'Return & refund policy',
                'excerpt' => "You have {$days} days to send something back if it is unopened.",
                'content' => <<<TXT
                ## The short version

                If an item is **unopened and unused**, you can send it back within **{$days} days** of delivery for a full refund of the item price.

                ## What we can take back

                - Unopened items with the seal intact
                - Anything that arrived damaged, wrong, or missing from your parcel
                - Anything whose batch code does not check out with the brand

                ## What we cannot take back

                - Opened skincare. Once a seal is broken we cannot resell it, and neither would you want us to
                - Items sent back after {$days} days
                - Gadgets with physical damage caused after delivery

                ## How to start a return

                1. Call or WhatsApp us on {$phone} within {$days} days, with your order number ready
                2. Keep the item in its original packaging
                3. We arrange the pickup, or tell you where to send it

                ## Getting your money back

                Once the item reaches us and we have checked it, we refund through bKash or Nagad within three working days. Delivery charges are not refunded unless the mistake was ours — if we sent the wrong item or it arrived damaged, you pay nothing.

                ## If something arrived wrong

                Tell us the same day if you can. Take a photo before opening anything further. We would rather fix it quickly than argue about it.
                TXT,
            ],

            'shipping-policy' => [
                'title'   => 'Shipping policy',
                'excerpt' => 'How long delivery takes, what it costs, and how you pay.',
                'content' => <<<TXT
                ## How you pay

                **Cash on delivery.** You pay the courier when the parcel reaches you — cash or bKash. Nothing is taken up front, and you are not charged if the parcel never arrives.

                ## How long it takes

                - Inside Dhaka city: usually the next day, often the same day
                - Dhaka sub-district: one to two days
                - Divisional cities: two days
                - Everywhere else in Bangladesh: two to three days

                Orders placed after 6pm start from the next working day. Friday and public holidays add a day.

                ## What it costs

                Delivery is charged by parcel size and where it is going. You see the exact amount at checkout before you confirm, never after.
                TXT . ($free > 0 ? "\n\n                Orders above ৳" . number_format($free) . " ship free anywhere in the country." : '') . <<<TXT


                ## Before the parcel goes out

                We call the number on your order to confirm it. If we cannot reach you after two attempts the order is held rather than shipped, so please keep your phone reachable.

                ## If the parcel does not arrive

                Call us on {$phone} with your order number. We chase the courier ourselves — you should not have to.
                TXT,
            ],

            'privacy-policy' => [
                'title'   => 'Privacy policy',
                'excerpt' => 'What we collect, what we do with it, and what we never do.',
                'content' => <<<TXT
                ## What we collect

                When you place an order we keep your name, mobile number, delivery address and, if you give one, your email address. If you make an account we also keep your password, stored scrambled so nobody here can read it.

                ## What we use it for

                - Delivering your order and calling you to confirm it
                - Sending order updates
                - Answering you when you contact us

                That is all. We do not build profiles for advertising.

                ## What we never do

                We do not sell your details, and we do not pass them to anyone except the courier carrying your parcel, who gets only your name, address and phone number.

                ## Payments

                Orders are cash on delivery. We never see or store card details.

                ## Your choices

                You can ask us to delete your account and your details at any time — call {$phone} or email {$email}. We keep the bare record of past orders for accounting, as the law requires, with nothing else attached.

                ## Cookies

                The site uses cookies only to keep you logged in and to remember what is in your cart. There is no advertising tracking.
                TXT,
            ],

            'terms' => [
                'title'   => 'Terms of service',
                'excerpt' => 'The rules of buying from us, in plain language.',
                'content' => <<<TXT
                ## Orders

                An order is confirmed once we have called you and you have agreed to it. Until then we may cancel it — most often because something has sold out between your order and our call.

                ## Prices and stock

                Prices are in Bangladeshi Taka and include everything except delivery. We try hard to keep stock numbers accurate, but if something sells out after you order we will tell you and refund or replace it, your choice.

                ## Cash on delivery

                You pay the courier on delivery. Refusing a parcel repeatedly without reason may mean we stop accepting orders from that number, because each refusal costs us both courier legs.

                ## Products

                We sell genuine imported products and say where each one comes from. We are not the manufacturer, and results from any skincare product vary from person to person. Nothing on this site is medical advice — if you have a skin condition, see a doctor.

                ## Returns

                Covered in our return policy.

                ## Getting in touch

                {$name}, {$address}. Phone {$phone}, email {$email}.
                TXT,
            ],

            'contact' => [
                'title'   => 'Contact us',
                'excerpt' => 'Call, WhatsApp or email — we answer.',
                'content' => <<<TXT
                ## Talk to a person

                The fastest way to reach us is the phone. We are a small team and we answer our own calls.

                **Phone and WhatsApp:** {$phone}
                **Email:** {$email}

                ## Where we are

                {$address}

                ## When we answer

                Saturday to Thursday, 10am to 8pm. Friday we are slower but we still read messages.

                ## Asking about an order

                Have your order number ready — it looks like AMJR-260903-1234 and is on your confirmation. With that we can tell you exactly where your parcel is.
                TXT,
            ],
        ];

        if (! isset($templates[$key])) {
            return null;
        }

        $template = $templates[$key];
        $template['slug'] = $key;

        // Heredocs are indented for readability; strip that before saving.
        $template['content'] = preg_replace('/^[ \t]+/m', '', $template['content']);

        return $template;
    }
}
