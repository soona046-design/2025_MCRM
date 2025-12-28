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
        Schema::create('treatment_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique()->comment('진료 유형 코드 (예: implant, orthodontic)');
            $table->string('name', 100)->comment('진료 유형 이름 (예: 임플란트, 교정)');
            $table->string('category', 50)->nullable()->comment('대분류 (예: 보철, 교정, 보존, 미용)');
            $table->string('color', 7)->default('#3b82f6')->comment('그래프 표시용 색상');
            $table->integer('sort_order')->default(0)->comment('정렬 순서');
            $table->boolean('active')->default(true)->comment('활성화 여부');
            $table->text('description')->nullable()->comment('진료 유형 설명');
            $table->timestamps();

            $table->index('category');
            $table->index('active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('treatment_types');
    }
};
