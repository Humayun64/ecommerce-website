<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // What the item cost us to land in Bangladesh. Drives margin reporting.
            $table->decimal('cost_price', 10, 2)->nullable()->after('compare_price');

            // Open Graph — controls how the product looks when shared on Facebook.
            $table->string('og_title')->nullable()->after('meta_description');
            $table->string('og_description')->nullable()->after('og_title');
            $table->string('og_image')->nullable()->after('og_description');

            $table->string('canonical_url')->nullable()->after('og_image');
            $table->boolean('is_indexable')->default(true)->after('canonical_url');
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->decimal('cost_price', 10, 2)->nullable()->after('compare_price');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'cost_price', 'og_title', 'og_description',
                'og_image', 'canonical_url', 'is_indexable',
            ]);
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn('cost_price');
        });
    }
};
