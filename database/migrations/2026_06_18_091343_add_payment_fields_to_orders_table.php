<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_method')->nullable()->after('status')->comment('card или sbp');
            $table->string('payment_id')->nullable()->after('payment_method')->comment('ID платежа в Robokassa');
            $table->timestamp('paid_at')->nullable()->after('payment_id');
        });
    }

    public function down(): void {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'payment_id', 'paid_at']);
        });
    }
};