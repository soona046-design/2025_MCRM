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
    Schema::create('tickets', function (Blueprint $table) {
        $table->uuid('ticket_id')->primary(); // ticket_id (UUID 기본값 사용)
        $table->uuid('lead_id'); // leads 테이블의 lead_id 참조
        $table->uuid('assignee_id')->nullable(); // users 테이블의 user_id 참조 (담당자)
        $table->string('state')->default('신규'); // 상태 (예: '신규', '진행', '보류', '완료')
        $table->string('priority')->default('일반'); // 우선순위 (예: '긴급', '높음', '일반', '낮음')
        $table->json('tags')->nullable(); // tags (JSON 형식)
        $table->text('notes')->nullable(); // notes (상담 메모)
        $table->timestamp('last_contact_at')->nullable(); // last_contact_at
        $table->timestamps();

        // 외래 키 제약 조건
        $table->foreign('lead_id')->references('lead_id')->on('leads')->onDelete('cascade');
        $table->foreign('assignee_id')->references('user_id')->on('users')->onDelete('set null');
    });
}

// ... existing code ...

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
