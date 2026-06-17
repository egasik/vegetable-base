<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('products', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('category_id')->index();
            $table->string('name');
            $table->text('description');
            
            // Розничные настройки
            $table->boolean('is_retail')->default(true)->comment('Доступна ли розничная продажа');
            $table->decimal('retail_price', 8, 2)->nullable()->comment('Цена за 1 кг/единицу');
            $table->string('image_path')->nullable()->comment('Путь к изображению товара');
            // Оптовые настройки
            $table->boolean('is_wholesale')->default(false)->comment('Доступна ли оптовая продажа');
            $table->integer('wholesale_unit_kg')->nullable()->comment('Вес оптовой единицы (10, 20 или 50 кг)');
            $table->decimal('wholesale_price', 8, 2)->nullable()->comment('Цена за весь оптовой мешок');
            
            $table->timestamps();

            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
        });
    }

    public function down(): void {
        Schema::dropIfExists('products');
    }
};