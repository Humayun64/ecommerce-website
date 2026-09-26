<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\ContactPage;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function __construct(private ContactPage $page)
    {
    }

    public function edit()
    {
        return view('admin.contact.edit', [
            'settings' => Setting::all_cached(),
            'page'     => $this->page,
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'contact_heading'    => ['nullable', 'string', 'max:80'],
            'contact_intro'      => ['nullable', 'string', 'max:400'],
            'contact_reply_time' => ['nullable', 'string', 'max:160'],
            'contact_hours'      => ['nullable', 'string', 'max:600'],
            'contact_topics'     => ['nullable', 'string', 'max:600'],
            'contact_map'        => ['nullable', 'string', 'max:2000'],
        ]);

        // People paste the whole <iframe> from Google rather than just the URL,
        // so take the src out of it rather than telling them off.
        $pasted = ContactPage::extractMapSrc($data['contact_map'] ?? null);
        $map    = ContactPage::allowedMap($pasted);

        // Only a real Google Maps embed is stored, because this value becomes
        // an iframe src on a public page.
        $data['contact_map'] = $map ?? '';

        $data['contact_form_enabled'] = $request->boolean('contact_form_enabled') ? '1' : '0';

        Setting::put($data);

        $saved = __('Contact page saved.');

        if ($pasted !== null && $map === null) {
            $saved .= ' ' . __('The map was not kept — paste the Embed a map code from Google Maps, which is an https://www.google.com/maps/embed link.');
        }

        return back()->with('status', $saved);
    }
}
