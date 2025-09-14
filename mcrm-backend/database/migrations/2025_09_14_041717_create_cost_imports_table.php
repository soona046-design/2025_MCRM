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
    Schema::create('cost_imports', function (Blueprint $table) {
        $table->id(); // Laravel 기본 ID 사용 (auto-increment)
        $table->string('platform'); // 플랫폼 (예: 'naver', 'google', 'meta')
        $table->string('campaign_code');
        $table->date('date'); // 비용 데이터의 날짜
        $table->integer('impressions')->default(0); // 노출 수
        $table->integer('clicks')->default(0); // 클릭 수
        $table->decimal('cost', 10, 2)->default(0.00); // 비용
        $table->timestamps();

        // 복합 유니크 키 (platform, campaign_code, date 조합으로 유니크)
        $table->unique(['platform', 'campaign_code', 'date']);
    });
}

// ... existing code ...

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cost_imports');
    }
};
