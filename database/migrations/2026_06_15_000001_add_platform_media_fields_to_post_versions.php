<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('post_versions', function (Blueprint $table) {
            $table->json('platform_media_ids')->nullable()->after('media');
            $table->string('media_status')->default('pending')->after('platform_media_ids');
        });
    }

    public function down(): void
    {
        Schema::table('post_versions', function (Blueprint $table) {
            $table->dropColumn(['platform_media_ids', 'media_status']);
        });
    }
};
