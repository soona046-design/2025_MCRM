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
        Schema::table('visits', function (Blueprint $table) {
            $table->enum('channel_category', ['online', 'offline', 'db'])
                  ->nullable()
                  ->after('utm_campaign')
                  ->comment('채널 카테고리: online, offline, db');

            $table->index('channel_category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            $table->dropIndex(['channel_category']);
            $table->dropColumn('channel_category');
        });
    }
};

