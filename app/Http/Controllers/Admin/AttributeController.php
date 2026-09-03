<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AttributeController extends Controller
{
    public function index()
    {
        $attributes = Attribute::with('values')
            ->withCount('products')
            ->orderBy('sort_order')
            ->get();

        return view('admin.attributes.index', compact('attributes'));
    }

    public function create()
    {
        return view('admin.attributes.create', [
            'attribute' => new Attribute(['is_filterable' => true]),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $attribute = DB::transaction(function () use ($data, $request) {
            $attribute = Attribute::create($data);
            $this->syncValues($attribute, $request->input('values', []));

            return $attribute;
        });

        return redirect()->route('admin.attributes.index')
            ->with('status', "{$attribute->name} created.");
    }

    public function edit(Attribute $attribute)
    {
        $attribute->load('values');

        return view('admin.attributes.edit', compact('attribute'));
    }

    public function update(Request $request, Attribute $attribute)
    {
        $data = $this->validated($request, $attribute);

        DB::transaction(function () use ($attribute, $data, $request) {
            $attribute->update($data);
            $this->syncValues($attribute, $request->input('values', []));
        });

        return redirect()->route('admin.attributes.index')
            ->with('status', "{$attribute->name} updated.");
    }

    public function destroy(Attribute $attribute)
    {
        if ($attribute->products()->exists()) {
            return back()->with('error', 'Products are still using this attribute. Remove it from them first.');
        }

        $attribute->delete();

        return back()->with('status', 'Attribute deleted.');
    }

    private function validated(Request $request, ?Attribute $attribute = null): array
    {
        $request->merge([
            'slug'       => $request->filled('slug') ? $request->slug : null,
            'sort_order' => $request->filled('sort_order') ? $request->sort_order : 0,
        ]);

        $data = $request->validate([
            'name'       => ['required', 'string', 'max:60'],
            'slug'       => ['nullable', 'string', 'max:70', Rule::unique('attributes')->ignore($attribute)],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'values'     => ['array'],
            'values.*.id'    => ['nullable', 'integer'],
            'values.*.value' => ['nullable', 'string', 'max:60'],
        ]);

        $data['is_filterable'] = $request->boolean('is_filterable');

        unset($data['values']);

        return $data;
    }

    private function syncValues(Attribute $attribute, array $rows): void
    {
        $keep  = [];
        $order = 1;

        foreach ($rows as $row) {
            $label = trim($row['value'] ?? '');

            if ($label === '') {
                continue;
            }

            $payload = [
                'value'      => $label,
                'slug'       => Str::slug($label),
                'sort_order' => $order++,
            ];

            if (filled($row['id'] ?? null) && $existing = $attribute->values()->find($row['id'])) {
                $existing->update($payload);
                $keep[] = $existing->id;
            } else {
                $keep[] = $attribute->values()->create($payload)->id;
            }
        }

        // A value still used by a variation is kept — deleting it would
        // silently break that variation's identity.
        $attribute->values()
            ->whereNotIn('id', $keep ?: [0])
            ->whereDoesntHave('variants')
            ->delete();
    }
}
