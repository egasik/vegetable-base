<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('orders', function (Blueprint $table) {
            $table->text('delivery_address')->nullable()->after('total_amount');
            $table->string('delivery_city')->default('Иркутск')->after('delivery_address');
            $table->string('delivery_region')->default('Иркутская область')->after('delivery_city');
            $table->decimal('latitude', 10, 8)->nullable()->after('delivery_region');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
        });
    }

    public function down(): void {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['delivery_address', 'delivery_city', 'delivery_region', 'latitude', 'longitude']);
        });
    }
};