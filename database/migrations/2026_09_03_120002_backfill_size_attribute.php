<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Converts the flat variant labels created in Batch 2 into structured
 * Size values, so nothing has to be re-entered by hand.
 *
 * "30 ml" stops being a string on the variant and becomes a value of
 * the Size attribute, linked through the pivot.
 */
return new class extends Migration
{
    public function up(): void
    {
        $variants = DB::table('product_variants')->orderBy('id')->get();

        if ($variants->isEmpty()) {
            return;
        }

        $attributeId = DB::table('attributes')->where('slug', 'size')->value('id');

        if (! $attributeId) {
            $attributeId = DB::table('attributes')->insertGetId([
                'name'          => 'Size',
                'slug'          => 'size',
                'sort_order'    => 1,
                'is_filterable' => true,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        }

        $valueIds = [];
        $order    = 1;

        foreach ($variants as $variant) {
            $label = trim($variant->name);

            if ($label === '') {
                continue;
            }

            $slug = Str::slug($label) ?: 'value-' . $variant->id;

            if (! isset($valueIds[$slug])) {
                $existing = DB::table('attribute_values')
                    ->where('attribute_id', $attributeId)
                    ->where('slug', $slug)
                    ->value('id');

                $valueIds[$slug] = $existing ?: DB::table('attribute_values')->insertGetId([
                    'attribute_id' => $attributeId,
                    'value'        => $label,
                    'slug'         => $slug,
                    'sort_order'   => $order++,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
            }

            DB::table('attribute_value_product_variant')->insertOrIgnore([
                'product_variant_id' => $variant->id,
                'attribute_value_id' => $valueIds[$slug],
            ]);

            DB::table('product_attribute')->insertOrIgnore([
                'product_id'   => $variant->product_id,
                'attribute_id' => $attributeId,
                'sort_order'   => 1,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('attribute_value_product_variant')->delete();
        DB::table('product_attribute')->delete();
        DB::table('attribute_values')->delete();
        DB::table('attributes')->where('slug', 'size')->delete();
    }
};
