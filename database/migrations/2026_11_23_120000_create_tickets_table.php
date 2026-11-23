<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('tickets')) {
            Schema::create('tickets', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->string('subject');
                $table->string('category')->default('technical'); // technical, course, account, suggestion, other
                $table->string('status')->default('pending'); // pending, answered, closed
                $table->string('priority')->default('medium'); // low, medium, high
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('tickets');
    }
};
