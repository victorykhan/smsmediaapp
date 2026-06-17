<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('social_account_id')->constrained()->cascadeOnDelete();
            $table->string('platform');
            $table->text('content')->nullable();
            $table->json('media')->nullable();
            $table->string('status')->default('draft');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->string('platform_post_id')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['post_id', 'platform']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_versions');
    }
};
