<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\WhatsApp;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WhatsAppController extends Controller
{
    public function __construct(private WhatsApp $whatsapp)
    {
    }

    public function edit()
    {
        $s = $this->whatsapp->all();

        return view('admin.whatsapp.edit', [
            's'          => $s,
            'hasKey'     => $s['key'] !== '',
            'chatReady'  => $this->whatsapp->chatReady(),
            'alertReady' => $this->whatsapp->alertReady(),
            'providers'  => WhatsApp::PROVIDERS,
        ]);
    }

    public function update(Request $request)
    {
        $phone = ['nullable', 'string', 'max:24', 'regex:/^\+?[0-9][0-9 \-]{8,20}$/'];

        $data = $request->validate([
            'chat_on'       => ['nullable', 'boolean'],
            'chat_number'   => array_merge($phone, ['required_if:chat_on,1']),
            'chat_text'     => ['nullable', 'string', 'max:300'],
            'alert_on'      => ['nullable', 'boolean'],
            'provider'      => ['required', Rule::in(array_keys(WhatsApp::PROVIDERS))],
            'alert_number'  => array_merge($phone, ['required_if:alert_on,1']),
            'key'           => ['nullable', 'string', 'max:600', 'regex:/^[A-Za-z0-9_\-.|]+$/'],
            'meta_phone_id' => ['nullable', 'string', 'max:30', 'regex:/^[0-9]+$/'],
            'meta_template' => ['nullable', 'string', 'max:120', 'regex:/^[a-z0-9_]+$/'],
            'meta_lang'     => ['nullable', 'string', 'max:10', 'regex:/^[a-z]{2}(_[A-Z]{2})?$/'],
        ], [
            'chat_number.regex'       => __('Enter the WhatsApp number with digits only, like 8801XXXXXXXXX.'),
            'alert_number.regex'      => __('Enter the WhatsApp number with digits only, like 8801XXXXXXXXX.'),
            'chat_number.required_if' => __('Enter the WhatsApp number customers should chat with.'),
            'alert_number.required_if'=> __('Enter the WhatsApp number that should get the order alerts.'),
            'key.regex'               => __('The key has characters that do not belong in it. Paste it again without spaces.'),
            'meta_template.regex'     => __('A template name is lowercase letters, numbers and underscores, like new_order_alert.'),
        ]);

        $this->whatsapp->save($data);

        if ($request->boolean('alert_on') && ! $this->whatsapp->alertReady()) {
            return back()->with('error', __('Saved, but order alerts are not sending yet: the number and the key are both needed (and the phone number ID and template for the Cloud API).'));
        }

        return back()->with('status', __('WhatsApp settings saved.'));
    }

    /** Send one real message, and show the provider's own words if it fails. */
    public function test()
    {
        if (! $this->whatsapp->alertReady()) {
            return back()->with('error', __('Fill in and switch on the order alerts first, then save, then test.'));
        }

        [$ok, $why] = $this->whatsapp->send(
            __('Test from your shop admin. Order alerts on WhatsApp are working.'),
            ['TEST-0001', __('Test customer'), 'Tk 0']
        );

        return $ok
            ? back()->with('status', __('Test sent. Check WhatsApp on the alert number.'))
            : back()->with('error', __('WhatsApp did not accept it: :why', ['why' => $why]));
    }
}
