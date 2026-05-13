<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lessons', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('category_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->longText('content_text')->nullable();
            $table->string('featured_image_url')->nullable();
            $table->string('video_url')->nullable();
            $table->string('podcast_url')->nullable();
            $table->string('video_type')->default('local'); // local, youtube, vimeo
            $table->integer('order_index')->default(0);
            $table->integer('estimated_time')->nullable(); // دقیقه
            $table->foreignUuid('prerequisite_id')->nullable()->references('id')->on('lessons');
            $table->boolean('is_published')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};