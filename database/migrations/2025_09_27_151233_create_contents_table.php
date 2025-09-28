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
        Schema::create('contents', function (Blueprint $table) {
            $table->id();
            $table->string('provider_id')->unique(); // Original ID from provider
            $table->string('provider_name'); // JSON or XML provider
            $table->string('title');
            $table->enum('type', ['video', 'article']);
            $table->json('tags'); // Store tags as JSON array
            $table->integer('views')->nullable();
            $table->integer('likes')->nullable();
            $table->string('duration')->nullable(); // For videos
            $table->integer('reading_time')->nullable(); // For articles
            $table->integer('reactions')->nullable(); // For articles
            $table->integer('comments')->nullable(); // For articles
            $table->datetime('published_at');
            $table->decimal('score', 8, 2)->default(0); // Calculated score
            $table->timestamps();
            
            $table->index(['type', 'score']);
            $table->index('published_at');
            $table->index('title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contents');
    }
};
