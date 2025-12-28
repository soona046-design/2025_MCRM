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
        Schema::create('marketing_insights', function (Blueprint $table) {
            $table->id();
            $table->date('analysis_period_start')->comment('분석 기간 시작일');
            $table->date('analysis_period_end')->comment('분석 기간 종료일');
            $table->enum('insight_type', ['channel_performance', 'treatment_trend', 'recommendation', 'roi_analysis'])->comment('인사이트 유형');
            $table->string('title', 200)->comment('인사이트 제목');
            $table->json('content')->nullable()->comment('AI 분석 결과 (JSON)');
            $table->json('recommendations')->nullable()->comment('마케팅 제안 리스트 (JSON)');
            $table->decimal('confidence_score', 5, 2)->nullable()->comment('신뢰도 점수 (0-100)');
            $table->uuid('generated_by')->nullable()->comment('생성자 user_id');
            $table->boolean('is_published')->default(false)->comment('공개 여부');
            $table->timestamps();

            $table->index(['analysis_period_start', 'analysis_period_end'], 'idx_analysis_period');
            $table->index('insight_type');
            $table->index('is_published');

            $table->foreign('generated_by')->references('user_id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marketing_insights');
    }
};
