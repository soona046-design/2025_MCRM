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
    Schema::create('communications', function (Blueprint $table) {
        $table->uuid('comm_id')->primary(); // comm_id (UUID 기본값 사용)
        $table->uuid('ticket_id'); // tickets 테이블의 ticket_id 참조
        $table->string('type'); // type (예: 'sms', 'kakao', 'call', 'chat')
        $table->string('direction'); // direction (예: 'inbound', 'outbound')
        $table->text('content')->nullable(); // 내용
        $table->json('meta')->nullable(); // 추가 메타데이터 (JSON 형식)
        $table->timestamp('at'); // 발생 시각
        $table->timestamps();

        // 외래 키 제약 조건
        $table->foreign('ticket_id')->references('ticket_id')->on('tickets')->onDelete('cascade');
    });
}

// ... existing code ...

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('communications');
    }
};
