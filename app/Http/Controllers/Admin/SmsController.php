<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\SmsLog;
use App\Services\SmsGateway;
use App\Services\SmsNotifier;
use Illuminate\Http\Request;

class SmsController extends Controller
{
    public function __construct(
        private SmsGateway $gateway,
        private SmsNotifier $notifier,
    ) {
    }

    public function index(Request $request)
    {
        $logs = SmsLog::with('order')
            ->status($request->input('status'))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.sms.index', [
            'settings'  => Setting::all_cached(),
            'gateway'   => $this->gateway,
            'notifier'  => $this->notifier,
            'presets'   => SmsGateway::PRESETS,
            'events'    => $this->notifier->estimateParts(),
            'logs'      => $logs,
            'sentMonth' => SmsLog::where('status', 'sent')
                ->where('created_at', '>=', now()->startOfMonth())
                ->sum('parts'),
            'failed'    => SmsLog::where('status', 'failed')->count(),
        ]);
    }

    public function updateGateway(Request $request)
    {
        $data = $request->validate([
            'sms_url'     => ['nullable', 'url', 'max:300'],
            'sms_api_key' => ['nullable', 'string', 'max:300'],
            'sms_sender'  => ['nullable', 'string', 'max:40'],
            'sms_method'  => ['required', 'in:GET,POST'],
            'sms_extra'   => ['nullable', 'string', 'max:1000'],
            'field_api_key' => ['required', 'string', 'max:40'],
            'field_to'      => ['required', 'string', 'max:40'],
            'field_text'    => ['required', 'string', 'max:40'],
            'field_from'    => ['nullable', 'string', 'max:40'],
        ]);

        Setting::put([
            'sms_enabled' => $request->boolean('sms_enabled') ? '1' : '0',
            'sms_url'     => $data['sms_url'] ?? '',
            'sms_api_key' => $data['sms_api_key'] ?? '',
            'sms_sender'  => $data['sms_sender'] ?? '',
            'sms_method'  => $data['sms_method'],
            'sms_extra'   => $data['sms_extra'] ?? '',
            'sms_fields'  => json_encode([
                'api_key' => $data['field_api_key'],
                'to'      => $data['field_to'],
                'text'    => $data['field_text'],
                'from'    => $data['field_from'] ?? '',
            ]),
        ]);

        return back()->with('status', __('Gateway saved.'));
    }

    public function updateTemplates(Request $request)
    {
        $save = [];

        foreach (array_keys(SmsNotifier::EVENTS) as $event) {
            $save["sms_tpl_{$event}"] = trim((string) $request->input("tpl_{$event}", ''));
            $save["sms_on_{$event}"]  = $request->boolean("on_{$event}") ? '1' : '0';
        }

        $request->validate(
            array_fill_keys(
                array_map(fn ($e) => "tpl_{$e}", array_keys(SmsNotifier::EVENTS)),
                ['nullable', 'string', 'max:600']
            )
        );

        Setting::put($save);

        return back()->with('status', __('Messages saved.'));
    }

    /** Send one to yourself before trusting it with a customer. */
    public function test(Request $request)
    {
        $data = $request->validate([
            'phone' => ['required', 'string', 'max:32'],
        ]);

        $log = $this->gateway->send(
            $data['phone'],
            __('Test message from :store. If you can read this, your SMS setup works.', [
                'store' => Setting::get('store_name', 'AMJR Global'),
            ]),
            'test'
        );

        return $log->status === 'sent'
            ? back()->with('status', __('Sent. Check the phone.'))
            : back()->with('error', __('Failed: :why', ['why' => $log->response ?: 'no reply from the gateway']));
    }
}
