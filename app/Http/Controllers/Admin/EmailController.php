<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\MailSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class EmailController extends Controller
{
    public function __construct(private MailSettings $mail)
    {
    }

    public function edit()
    {
        $s = $this->mail->all();

        return view('admin.email.edit', [
            's'           => $s,
            'hasPassword' => $s['password'] !== '',
            'ready'       => $this->mail->ready(),
            'envMailer'   => config('mail.default'),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'on'           => ['nullable', 'boolean'],
            'host'         => ['nullable', 'string', 'max:120', 'regex:/^[A-Za-z0-9.\-]+$/'],
            'port'         => ['nullable', 'integer', 'min:1', 'max:65535'],
            'security'     => ['nullable', Rule::in(['ssl', 'tls'])],
            'username'     => ['nullable', 'string', 'max:160'],
            'password'     => ['nullable', 'string', 'max:300'],
            'from_address' => ['nullable', 'email', 'max:160'],
            'from_name'    => ['nullable', 'string', 'max:80'],
            'admin_copy'   => ['nullable', 'boolean'],
            'admin_to'     => ['nullable', 'email', 'max:160', 'required_if:admin_copy,1'],
        ], [
            'host.regex'           => __('The mail server is a name like smtp.hostinger.com — no http:// and no spaces.'),
            'admin_to.required_if' => __('Enter the address the order copies should go to.'),
        ]);

        $this->mail->save($data);

        if ($request->boolean('on') && ! $this->mail->ready()) {
            return back()->with('error', __('Saved, but email is not sending yet: the server, username, password and "from" address are all needed.'));
        }

        return back()->with('status', __('Email settings saved.'));
    }

    /** Send one real email, and show the mail server's own words if it fails. */
    public function test(Request $request)
    {
        $data = $request->validate(['to' => ['required', 'email', 'max:160']]);

        if (! $this->mail->ready()) {
            return back()->with('error', __('Fill in and switch on the settings first, then save, then test.'));
        }

        try {
            Mail::raw(
                __("This is a test from your shop's admin panel.\n\nIf you are reading it, outgoing email is working.") . "\n\n" . url('/'),
                fn ($message) => $message->to($data['to'])->subject(__('Test email from :shop', ['shop' => config('mail.from.name')]))
            );
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', __('The mail server refused: :why', ['why' => Str::limit($e->getMessage(), 400)]));
        }

        return back()->with('status', __('Test email sent to :to. Check the inbox, and the spam folder too.', ['to' => $data['to']]));
    }
}
