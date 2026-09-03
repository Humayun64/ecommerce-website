<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    /** Every editable key, so nothing outside this list can be written. */
    private const KEYS = [
        'store_name', 'store_tagline', 'store_email', 'store_phone',
        'store_whatsapp', 'store_address',
        'topbar_message', 'hero_eyebrow', 'hero_heading', 'hero_text', 'footer_about',
        'delivery_dhaka', 'delivery_suburb', 'delivery_city', 'delivery_outside',
        'free_delivery_over', 'return_days',
        'facebook_url', 'instagram_url', 'youtube_url',
        'meta_title', 'meta_description',
    ];

    public function edit()
    {
        return view('admin.settings.edit', [
            'settings' => Setting::all_cached(),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'store_name'         => ['required', 'string', 'max:80'],
            'store_tagline'      => ['nullable', 'string', 'max:120'],
            'store_email'        => ['nullable', 'email', 'max:120'],
            'store_phone'        => ['nullable', 'string', 'max:40'],
            'store_whatsapp'     => ['nullable', 'string', 'max:20'],
            'store_address'      => ['nullable', 'string', 'max:200'],

            'topbar_message'     => ['nullable', 'string', 'max:160'],
            'hero_eyebrow'       => ['nullable', 'string', 'max:60'],
            'hero_heading'       => ['nullable', 'string', 'max:90'],
            'hero_text'          => ['nullable', 'string', 'max:300'],
            'footer_about'       => ['nullable', 'string', 'max:300'],

            'delivery_dhaka'     => ['nullable', 'numeric', 'min:0'],
            'delivery_suburb'    => ['nullable', 'numeric', 'min:0'],
            'delivery_city'      => ['nullable', 'numeric', 'min:0'],
            'delivery_outside'   => ['nullable', 'numeric', 'min:0'],
            'free_delivery_over' => ['nullable', 'numeric', 'min:0'],
            'return_days'        => ['nullable', 'integer', 'min:0', 'max:90'],

            'facebook_url'       => ['nullable', 'url', 'max:200'],
            'instagram_url'      => ['nullable', 'url', 'max:200'],
            'youtube_url'        => ['nullable', 'url', 'max:200'],

            'meta_title'         => ['nullable', 'string', 'max:70'],
            'meta_description'   => ['nullable', 'string', 'max:180'],

            'logo'               => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp,svg', 'max:2048'],
            'favicon'            => ['nullable', 'image', 'mimes:png,ico,jpg', 'max:512'],
        ]);

        $pairs = [];

        foreach (self::KEYS as $key) {
            $pairs[$key] = $data[$key] ?? null;
        }

        // Logos keep their original format — re-encoding a PNG as JPEG
        // would turn a transparent background black.
        foreach (['logo', 'favicon'] as $file) {
            if ($request->hasFile($file)) {
                $old = Setting::get($file);

                if ($old && Storage::disk('public')->exists($old)) {
                    Storage::disk('public')->delete($old);
                }

                $pairs[$file] = $request->file($file)->store('branding', 'public');
            }
        }

        Setting::put($pairs);

        return back()->with('status', __('Settings saved.'));
    }
}
