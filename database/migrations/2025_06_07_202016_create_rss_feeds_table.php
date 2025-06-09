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
        Schema::create('rss_feeds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('news_source_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('url');
            $table->string('category')->default('general');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_fetched_at')->nullable();
            $table->timestamps();
            
            $table->index(['news_source_id', 'is_active']);
            $table->index(['category', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rss_feeds');
    }
};
