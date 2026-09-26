<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Services\ContactPage;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ContactController extends Controller
{
    public function __construct(private ContactPage $page)
    {
    }

    public function show()
    {
        return view('site.contact.show', [
            'page'   => $this->page,
            'topics' => $this->page->topics(),
            // Used to measure how long the form was on screen. A bot posts
            // the moment it loads; a person takes a few seconds to type.
            'opened' => encrypt(now()->timestamp),
        ]);
    }

    public function store(Request $request)
    {
        abort_unless($this->page->formEnabled(), 404);

        $this->guardSpam($request);

        $data = $request->validate([
            'name'         => ['required', 'string', 'max:80'],
            'phone'        => ['required', 'string', 'max:32'],
            'email'        => ['nullable', 'email', 'max:120'],
            'topic'        => ['nullable', 'string', 'max:80'],
            'order_number' => ['nullable', 'string', 'max:40'],
            'message'      => ['required', 'string', 'min:10', 'max:4000'],
        ], [
            'phone.required'  => __('We need a number to call you back on.'),
            'message.min'     => __('Tell us a little more so we can actually help.'),
        ]);

        $message = ContactMessage::create($data + [
            'user_id'    => auth()->id(),
            'phone'      => $this->normalisePhone($data['phone']),
            'status'     => 'new',
            'ip'         => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
        ]);

        return redirect()
            ->route('contact')
            ->with('sent', $message->id);
    }

    /**
     * Two checks, neither of which a real person ever notices.
     *
     * The honeypot is a field hidden with CSS: a browser leaves it empty,
     * most bots fill everything in. The timer catches the rest — nobody
     * types a real message in under three seconds.
     */
    private function guardSpam(Request $request): void
    {
        if (trim((string) $request->input('website')) !== '') {
            throw ValidationException::withMessages([
                'message' => __('That did not go through. Please try again.'),
            ]);
        }

        try {
            $opened = (int) decrypt($request->input('opened'));
        } catch (\Throwable $e) {
            $opened = 0;
        }

        if ($opened > 0 && (now()->timestamp - $opened) < 3) {
            throw ValidationException::withMessages([
                'message' => __('That was a little too quick. Please send it again.'),
            ]);
        }
    }

    private function normalisePhone(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);

        if (str_starts_with($digits, '880')) {
            $digits = '0' . substr($digits, 3);
        }

        return $digits ?: $phone;
    }
}
