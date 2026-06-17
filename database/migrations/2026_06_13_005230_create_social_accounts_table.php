<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('platform');
            $table->string('account_name');
            $table->string('account_id')->nullable();
            $table->string('avatar_url')->nullable();
            $table->text('credentials')->nullable();
            $table->json('settings')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['client_id', 'platform']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_accounts');
    }
};
