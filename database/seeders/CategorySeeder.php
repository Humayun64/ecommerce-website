<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $tree = [
            'Skincare' => ['Cleansers', 'Toners', 'Serums', 'Moisturisers', 'Sunscreen'],
            'Gadgets'  => ['Torches', 'Power Banks', 'Cables'],
        ];

        $parentOrder = 1;

        foreach ($tree as $parentName => $children) {
            $parent = Category::updateOrCreate(
                ['slug' => Str::slug($parentName)],
                ['name' => $parentName, 'parent_id' => null, 'sort_order' => $parentOrder++]
            );

            $childOrder = 1;

            foreach ($children as $childName) {
                Category::updateOrCreate(
                    ['slug' => Str::slug($childName)],
                    ['name' => $childName, 'parent_id' => $parent->id, 'sort_order' => $childOrder++]
                );
            }
        }
    }
}
