<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('user_cards', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            
            // Внешний ключ с явным индексом для MySQL 8.4
            $table->unsignedBigInteger('user_id')->index();
            
            // Поля будут хранить ЗАШИФРОВАННЫЕ строки, поэтому используем string/text
            $table->string('card_number')->comment('Зашифрованный номер карты (PAN)');
            $table->string('cvc_code')->comment('Зашифрованный CVC/CVV код');
            $table->string('pin_code')->comment('Зашифрованный PIN-код');
            
            $table->boolean('is_default')->default(false)->comment('Использовать по умолчанию');
            $table->timestamps();

            // Явное создание внешнего ключа с каскадным удалением
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void {
        Schema::dropIfExists('user_cards');
    }
};