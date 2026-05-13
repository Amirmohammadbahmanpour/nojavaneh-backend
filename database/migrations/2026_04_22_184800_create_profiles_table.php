<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->onDelete('cascade');
            $table->string('full_name')->nullable();
            $table->integer('age')->nullable();
            $table->string('education')->nullable(); // bachelor, master, diploma
            $table->string('economic_status')->nullable(); // good, average
            $table->integer('number_of_children')->default(0);
            $table->json('children_ages')->nullable(); // سن فرزندان به صورت JSON
            $table->boolean('has_attended_workshop')->default(false);
            $table->string('role')->default('user'); // user, admin
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};