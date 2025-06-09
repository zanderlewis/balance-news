<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('news_source_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('summary')->nullable();
            $table->string('url')->unique();
            $table->string('image_url')->nullable();
            $table->string('author')->nullable();
            $table->timestamp('published_at');
            $table->json('keywords')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['published_at', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
