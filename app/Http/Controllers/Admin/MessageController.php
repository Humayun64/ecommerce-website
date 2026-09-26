<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status');

        $rows = ContactMessage::query()
            ->when($status === 'spam', fn ($q) => $q->where('status', 'spam'))
            ->when($status && $status !== 'spam', fn ($q) => $q->where('status', $status))
            ->when(! $status, fn ($q) => $q->inbox())
            ->search($request->input('search'))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $counts = ContactMessage::selectRaw('status, count(*) as n')->groupBy('status')->pluck('n', 'status');

        return view('admin.messages.index', [
            'rows'     => $rows,
            'counts'   => [
                'new'     => (int) ($counts['new'] ?? 0),
                'read'    => (int) ($counts['read'] ?? 0),
                'replied' => (int) ($counts['replied'] ?? 0),
                'spam'    => (int) ($counts['spam'] ?? 0),
            ],
            'statuses' => ContactMessage::STATUSES,
        ]);
    }

    public function show(ContactMessage $message)
    {
        // Opening it is what "read" means, so no extra click is needed.
        if ($message->status === 'new') {
            $message->update([
                'status'     => 'read',
                'read_at'    => now(),
                'handled_by' => auth()->id(),
            ]);
        }

        $message->load(['user', 'handler']);

        return view('admin.messages.show', ['row' => $message]);
    }

    public function update(Request $request, ContactMessage $message)
    {
        $data = $request->validate([
            'action'     => ['required', 'in:replied,read,spam,unspam'],
            'admin_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $note = $data['admin_note'] ?? null;

        $changes = match ($data['action']) {
            'replied' => ['status' => 'replied', 'replied_at' => now()],
            'read'    => ['status' => 'read', 'replied_at' => null],
            'spam'    => ['status' => 'spam'],
            'unspam'  => ['status' => 'read'],
        };

        $message->update($changes + [
            'admin_note' => $note ?: $message->admin_note,
            'handled_by' => auth()->id(),
            'read_at'    => $message->read_at ?: now(),
        ]);

        $said = [
            'replied' => __('Marked as replied.'),
            'read'    => __('Moved back to the inbox.'),
            'spam'    => __('Marked as spam.'),
            'unspam'  => __('Moved out of spam.'),
        ];

        return $data['action'] === 'spam'
            ? redirect()->route('admin.messages.index')->with('status', $said['spam'])
            : back()->with('status', $said[$data['action']]);
    }

    public function destroy(ContactMessage $message)
    {
        $message->delete();

        return redirect()->route('admin.messages.index')
            ->with('status', __('Message deleted.'));
    }
}
