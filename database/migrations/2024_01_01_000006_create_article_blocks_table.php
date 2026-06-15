<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('article_blocks', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            
            $table->unsignedBigInteger('article_id')->index(); // КРИТИЧНО: добавлен index()
            $table->enum('type', ['header', 'text', 'image']);
            $table->string('content')->nullable(); 
            $table->string('file_path')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('article_id')->references('id')->on('articles')->onDelete('cascade');
        });
    }

    public function down(): void {
        Schema::dropIfExists('article_blocks');
    }
};