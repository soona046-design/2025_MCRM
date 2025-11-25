<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 광고 성과 지표를 주차/월별로 저장하는 테이블
     */
    public function up(): void
    {
        Schema::create('ad_metrics', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // 플랫폼 및 채널 구분
            $table->enum('platform', ['naver', 'google', 'meta'])
                ->comment('광고 플랫폼');
            $table->enum('channel_type', [
                'keyword',      // 네이버 키워드 광고
                'place',        // 네이버 플레이스 광고
                'powercontent', // 네이버 파워컨텐츠 광고
                'gdn',          // 구글 디스플레이 네트워크
                'youtube',      // 유튜브 광고
                'sns'           // 메타(페이스북/인스타그램) SNS 광고
            ])->comment('채널 유형');

            // 기간 정보
            $table->enum('period_type', ['week', 'month'])
                ->comment('집계 단위');
            $table->string('period_label', 32)
                ->comment('기간 레이블 (예: 2025-W42, 2025-10-5w, 2025-08)');
            $table->date('date_start')->comment('집계 시작일');
            $table->date('date_end')->comment('집계 종료일');

            // 성과 지표
            $table->bigInteger('impressions')->default(0)
                ->comment('노출 수');
            $table->bigInteger('clicks')->default(0)
                ->comment('클릭 수');
            $table->decimal('ctr', 6, 3)->nullable()
                ->comment('클릭률 (%)');
            $table->integer('conversions')->default(0)
                ->comment('전환 수 (리드 또는 예약)');
            $table->integer('cost')->default(0)
                ->comment('광고 비용 (KRW)');
            $table->integer('cpl')->nullable()
                ->comment('리드당 비용 (Cost Per Lead)');
            $table->integer('cpa')->nullable()
                ->comment('예약당 비용 (Cost Per Appointment)');

            // 메타 데이터
            $table->json('meta_json')->nullable()
                ->comment('원본 API 응답 스냅샷');

            $table->timestamps();

            // Unique constraint: 동일 플랫폼/채널/기간 조합은 하나만 존재
            $table->unique(
                ['platform', 'channel_type', 'period_type', 'period_label'],
                'ad_metrics_unique_period'
            );

            // 인덱스
            $table->index('date_start');
            $table->index('date_end');
            $table->index(['platform', 'channel_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ad_metrics');
    }
};
