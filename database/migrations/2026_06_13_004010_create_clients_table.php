<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('website')->nullable();
            $table->string('industry')->nullable();
            $table->string('timezone')->default('UTC');
            $table->json('brand_colors')->nullable();
            $table->string('logo')->nullable();
            $table->unsignedTinyInteger('onboarding_step')->default(0);
            $table->boolean('is_onboarded')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
