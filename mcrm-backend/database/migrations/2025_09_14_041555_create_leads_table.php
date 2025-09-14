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
    Schema::create('leads', function (Blueprint $table) {
        $table->uuid('lead_id')->primary(); // lead_id (UUID 기본값 사용)
        $table->string('primary_phone')->nullable(); // primary_phone (민감 필드 암호화/마스킹 저장)
        $table->string('email_hash')->nullable()->unique(); // email_hash (Unique)
        $table->string('name')->nullable();
        $table->json('consent_flags')->nullable(); // JSON 형식으로 동의 플래그 저장
        $table->uuid('source_visit_id')->nullable(); // source_visit_id (visits 테이블의 visit_id 참조)
        $table->string('status')->default('new'); // status (예: 'new', 'qualified', 'converted', 'rejected')
        $table->integer('score')->default(0); // score (리드 스코어링)
        $table->timestamps(); // created_at, updated_at

        // 외래 키 제약 조건 (visits 테이블의 visit_id 참조)
        $table->foreign('source_visit_id')->references('visit_id')->on('visits')->onDelete('set null');
    });
}

// ... existing code ...

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
