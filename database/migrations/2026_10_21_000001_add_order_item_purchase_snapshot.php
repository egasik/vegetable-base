<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('quantity', 10, 3)->default(1)->change();
            $table->string('purchase_type', 16)->nullable();
            $table->string('unit', 16)->nullable();
            $table->decimal('unit_weight_kg', 10, 3)->nullable();
            $table->decimal('line_total', 12, 2)->nullable();
        });
    }

    public function down(): void
    {
        // Keep fractional quantities intact when reverting the snapshot fields.
        Schema::table('order_items', fn (Blueprint $table) => $table->dropColumn([
            'purchase_type', 'unit', 'unit_weight_kg', 'line_total',
        ]));
    }
};
