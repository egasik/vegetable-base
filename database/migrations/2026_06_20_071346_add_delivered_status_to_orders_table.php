<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // Добавляем новые статусы в enum
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending', 'paid', 'delivering', 'delivered', 'cancelled') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void {
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending', 'completed', 'cancelled') NOT NULL DEFAULT 'pending'");
    }
};