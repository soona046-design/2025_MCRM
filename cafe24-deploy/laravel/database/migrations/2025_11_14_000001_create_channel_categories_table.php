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
        // 카테고리 테이블
        Schema::create('channel_categories', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique()->comment('카테고리 코드: online, offline, db');
            $table->string('name', 50)->comment('카테고리 이름');
            $table->string('color', 7)->default('#000000')->comment('카테고리 색상');
            $table->integer('sort_order')->default(0)->comment('정렬 순서');
            $table->boolean('active')->default(true)->comment('활성화 여부');
            $table->timestamps();
        });

        // 채널-카테고리 매핑 테이블
        Schema::create('channel_category_mappings', function (Blueprint $table) {
            $table->id();
            $table->string('utm_source', 100)->comment('UTM Source 값');
            $table->string('display_name', 100)->nullable()->comment('표시용 이름');
            $table->foreignId('category_id')->constrained('channel_categories')->onDelete('cascade');
            $table->integer('priority')->default(0)->comment('매칭 우선순위');
            $table->boolean('active')->default(true)->comment('활성화 여부');
            $table->timestamps();

            $table->index('utm_source');
            $table->index('category_id');
            $table->unique(['utm_source', 'category_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('channel_category_mappings');
        Schema::dropIfExists('channel_categories');
    }
};
