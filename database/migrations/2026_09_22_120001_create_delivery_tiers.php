<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Size bands: Small, Medium, Heavy — and anything else he adds later.
        Schema::create('delivery_tiers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 60);
            $table->string('description')->nullable();
            $table->string('icon', 8)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        // One charge per size band, per zone.
        Schema::create('delivery_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_tier_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shipping_zone_id')->constrained()->cascadeOnDelete();
            $table->decimal('rate', 8, 2)->default(0);
            $table->timestamps();

            $table->unique(['delivery_tier_id', 'shipping_zone_id'], 'tier_zone_unique');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('delivery_tier_id')->nullable()->after('type')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['delivery_tier_id']);
            $table->dropColumn('delivery_tier_id');
        });

        Schema::dropIfExists('delivery_rates');
        Schema::dropIfExists('delivery_tiers');
    }
};
