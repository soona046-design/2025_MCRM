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
    Schema::create('lead_channels', function (Blueprint $table) {
        $table->uuid('lead_id'); // leads 테이블의 lead_id 참조
        $table->string('channel_code');
        $table->string('campaign_code')->nullable();
        $table->string('adgroup_code')->nullable();
        $table->string('creative_code')->nullable();
        $table->decimal('cost_attrib', 8, 2)->nullable(); // 비용 할당 (필요시 암호화 고려)
        $table->timestamps();

        // 복합 기본 키 설정 (lead_id, channel_code 조합으로 유니크하게 식별)
        $table->primary(['lead_id', 'channel_code']);

        // 외래 키 제약 조건 (leads 테이블의 lead_id 참조)
        $table->foreign('lead_id')->references('lead_id')->on('leads')->onDelete('cascade');
    });
}

// ... existing code ...

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_channels');
    }
};
