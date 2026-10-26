<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('orders', 'checkout_token')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->string('status', 24)->default('pending')->change();
                $table->string('payment_key', 64)->nullable();
                $table->timestamp('payment_key_created_at')->nullable();
                $table->uuid('checkout_token')->nullable()->unique();
                $table->string('delivery_address')->nullable();
                $table->decimal('latitude', 10, 7)->nullable();
                $table->decimal('longitude', 10, 7)->nullable();
                $table->string('delivery_details', 500)->nullable();
                $table->string('contact_phone', 30)->nullable();
            });
        }
        DB::table('orders')->where('status', 'completed')->update(['status' => 'paid']);
        DB::table('orders')->whereIn('status', ['processing', 'delivering'])->update(['status' => 'shipping']);
        Schema::create('verification_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('email');
            $table->string('code_hash');
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->dateTime('expires_at');
            $table->dateTime('sent_at');
            $table->timestamp('used_at')->nullable();
        });
        Schema::create('delivery_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('file_path');
            $table->string('comment', 500)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_photos');
        Schema::dropIfExists('verification_codes');
        Schema::table('orders', fn (Blueprint $table) => $table->dropColumn(['payment_key', 'payment_key_created_at', 'checkout_token', 'delivery_address', 'latitude', 'longitude', 'delivery_details', 'contact_phone']));
    }
};
