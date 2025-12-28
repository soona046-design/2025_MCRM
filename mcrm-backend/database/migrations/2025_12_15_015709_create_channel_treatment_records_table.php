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
        Schema::create('channel_treatment_records', function (Blueprint $table) {
            $table->id();
            $table->date('record_date')->comment('기록 날짜');
            $table->foreignId('channel_category_id')->constrained('channel_categories')->onDelete('cascade')->comment('내원 경로 (채널 카테고리)');
            $table->foreignId('treatment_type_id')->constrained('treatment_types')->onDelete('cascade')->comment('진료 유형');
            $table->integer('count')->default(0)->comment('건수');
            $table->decimal('revenue', 12, 2)->nullable()->comment('매출액');
            $table->text('notes')->nullable()->comment('메모');
            $table->enum('input_type', ['manual', 'auto'])->default('manual')->comment('입력 방식: manual=직접입력, auto=자동수집');
            $table->uuid('created_by')->nullable()->comment('입력자 user_id');
            $table->timestamps();

            // 복합 인덱스: 날짜별, 채널별 조회 최적화
            $table->index(['record_date', 'channel_category_id']);
            $table->index(['record_date', 'treatment_type_id']);
            $table->index('input_type');

            // 동일 날짜, 동일 채널, 동일 진료유형의 중복 방지
            $table->unique(['record_date', 'channel_category_id', 'treatment_type_id'], 'unique_record');

            // 외래 키
            $table->foreign('created_by')->references('user_id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('channel_treatment_records');
    }
};
