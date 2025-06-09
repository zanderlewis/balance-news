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
        Schema::create('news_sources', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('url');
            $table->string('rss_url')->nullable();
            $table->string('logo_url')->nullable();
            $table->string('bias_label')->default('center'); // left, lean-left, center, lean-right, right
            $table->string('country_code', 2)->default('US');
            $table->json('categories')->nullable(); // e.g., ['politics', 'business', 'tech']
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_scraped_at')->nullable();
            $table->timestamps();
            
            $table->index(['is_active', 'bias_label']);
            $table->index(['country_code', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('news_sources');
    }
};
