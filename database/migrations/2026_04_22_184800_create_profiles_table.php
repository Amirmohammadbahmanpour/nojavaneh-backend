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
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('full_name')->nullable();
            $table->integer('age')->nullable();
            $table->string('education')->nullable(); // bachelor, master, diploma
            $table->string('economic_status')->nullable(); // good, average
            $table->integer('number_of_children')->default(0);
            $table->json('children_ages')->nullable();
            $table->boolean('has_attended_workshop')->default(false);
            $table->string('role')->default('user'); // user, admin

            // فیلدهای تکمیلی پرسشنامه مادر و نوجوان
            $table->integer('mother_age')->nullable();
            $table->string('mother_education')->nullable();
            $table->string('father_education')->nullable();
            $table->string('mother_employment_status')->nullable();
            $table->string('family_income_status')->nullable();
            $table->integer('number_of_teenagers')->default(0);
            $table->string('teenager_gender')->nullable();
            $table->string('teenager_age_range')->nullable();
            $table->boolean('has_attended_parenting_course')->default(false);
            $table->string('teenager_phone_internet_usage')->nullable();
            $table->string('teenager_living_with')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
