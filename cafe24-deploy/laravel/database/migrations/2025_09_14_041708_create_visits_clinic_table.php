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
    Schema::create('visits_clinic', function (Blueprint $table) {
        $table->uuid('clinic_visit_id')->primary(); // clinic_visit_id (UUID 기본값 사용)
        $table->uuid('apt_id'); // appointments 테이블의 apt_id 참조
        $table->string('emr_visit_no')->nullable(); // EMR 방문 번호 (옵션)
        $table->string('procedure_code')->nullable(); // 시술 코드
        $table->decimal('charge_amount', 10, 2)->nullable(); // 수납 금액
        $table->timestamp('paid_at')->nullable(); // 수납 완료 시각
        $table->timestamps();

        // 외래 키 제약 조건
        $table->foreign('apt_id')->references('apt_id')->on('appointments')->onDelete('cascade');
    });
}

// ... existing code ...

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visits_clinic');
    }
};
