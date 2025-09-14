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
    Schema::create('appointments', function (Blueprint $table) {
        $table->uuid('apt_id')->primary(); // apt_id (UUID 기본값 사용)
        $table->uuid('lead_id'); // leads 테이블의 lead_id 참조
        $table->string('clinic_id')->nullable(); // clinic_id (지점 ID, users.clinic_id 와 연결 가능)
        $table->uuid('doctor_id')->nullable(); // users 테이블의 user_id 참조 (담당 의사/상담자)
        $table->timestamp('slot_at'); // 예약 슬롯 시간
        $table->string('status')->default('booked'); // 상태 (예: 'booked', 'noshow', 'done', 'cancelled')
        $table->boolean('reminder_sent')->default(false); // 리마인더 발송 여부
        $table->timestamps();

        // 외래 키 제약 조건
        $table->foreign('lead_id')->references('lead_id')->on('leads')->onDelete('cascade');
        $table->foreign('doctor_id')->references('user_id')->on('users')->onDelete('set null');
    });
}

// ... existing code ...

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
