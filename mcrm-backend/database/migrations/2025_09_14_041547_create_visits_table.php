<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
// ... existing code ...

public function up(): void
{
    Schema::create('visits', function (Blueprint $table) {
        $table->uuid('visit_id')->primary(); // visit_id (UUID 기본값 사용)
        $table->string('client_id')->nullable(); // GA4 client_id 유사 구조 (퍼스트파티 쿠키)
        $table->string('session_id')->nullable(); // session_id
        $table->string('utm_source')->nullable();
        $table->string('utm_medium')->nullable();
        $table->string('utm_campaign')->nullable();
        $table->string('utm_content')->nullable();
        $table->string('utm_term')->nullable();
        $table->string('referrer')->nullable();
        $table->string('landing_path')->nullable();
        $table->timestamp('first_seen_at')->nullable(); // first_seen_at
        $table->string('ip')->nullable();
        $table->text('ua')->nullable(); // User Agent
        $table->timestamps(); // created_at (updated_at은 필요시 추가)
    });
}

// ... existing code ...

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};
