<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MenuController extends Controller
{
    public function index(Request $request)
    {
        $location = in_array($request->location, array_keys(MenuItem::LOCATIONS))
            ? $request->location
            : 'header';

        return view('admin.menus.index', [
            'location' => $location,
            'items'    => MenuItem::for($location)->orderBy('column')->orderBy('sort_order')->get(),
            'pages'    => Page::published()->orderBy('title')->get(),
            'settings' => Setting::all_cached(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $data['sort_order'] = (int) MenuItem::where('location', $data['location'])
            ->where('column', $data['column'])->max('sort_order') + 1;

        MenuItem::create($data);

        return back()->with('status', __('Link added.'));
    }

    public function update(Request $request, MenuItem $item)
    {
        $item->update($this->validated($request));

        return back()->with('status', __('Link updated.'));
    }

    public function destroy(MenuItem $item)
    {
        $item->delete();

        return back()->with('status', __('Link removed.'));
    }

    /** Drag-and-drop reorder posts the ids in their new order. */
    public function reorder(Request $request)
    {
        $data = $request->validate([
            'order'   => ['required', 'array'],
            'order.*' => ['integer', 'exists:menu_items,id'],
        ]);

        foreach ($data['order'] as $position => $id) {
            MenuItem::whereKey($id)->update(['sort_order' => $position + 1]);
        }

        return $request->wantsJson()
            ? response()->json(['ok' => true])
            : back()->with('status', __('Order saved.'));
    }

    /** Footer column headings live in settings. */
    public function columns(Request $request)
    {
        $data = $request->validate([
            'footer_col1_title' => ['nullable', 'string', 'max:40'],
            'footer_col2_title' => ['nullable', 'string', 'max:40'],
        ]);

        Setting::put($data);

        return back()->with('status', __('Column headings saved.'));
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'location' => ['required', Rule::in(array_keys(MenuItem::LOCATIONS))],
            'column'   => ['nullable', 'integer', 'min:1', 'max:2'],
            'label'    => ['required', 'string', 'max:60'],
            'url'      => ['required', 'string', 'max:255'],
        ]);

        $data['column']    = $data['column'] ?? 1;
        $data['new_tab']   = $request->boolean('new_tab');
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
