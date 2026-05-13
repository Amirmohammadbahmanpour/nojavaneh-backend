<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lesson_likes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('lesson_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['lesson_id', 'user_id']); // هر کاربر فقط یک بار میتونه لایک کنه
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_likes');
    }
};