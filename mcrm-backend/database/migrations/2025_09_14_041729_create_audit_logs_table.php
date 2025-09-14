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
    Schema::create('audit_logs', function (Blueprint $table) {
        $table->id(); // Laravel 기본 ID 사용 (auto-increment)
        $table->uuid('actor_id')->nullable(); // users 테이블의 user_id 참조 (액션 수행자)
        $table->string('action'); // 수행된 액션 (예: 'create', 'update', 'delete', 'view', 'download')
        $table->string('target_type')->nullable(); // 대상 엔티티 타입 (예: 'lead', 'ticket', 'user')
        $table->uuid('target_id')->nullable(); // 대상 엔티티 ID
        $table->json('fields_masked')->nullable(); // 마스킹된 필드 정보 (JSON 형식)
        $table->timestamp('at')->useCurrent(); // 액션 발생 시각 (기본값 현재 시간)
        $table->string('ip')->nullable(); // 접속 IP
        $table->timestamps(); // created_at, updated_at (at 필드와 중복될 수 있으므로 필요에 따라 조정)

        // 외래 키 제약 조건 (옵션, actor_id가 users 테이블을 참조)
        // $table->foreign('actor_id')->references('user_id')->on('users')->onDelete('set null');
    });
}

// ... existing code ...

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
