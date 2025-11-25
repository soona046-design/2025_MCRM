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
        Schema::create('visits_clinics', function (Blueprint $table) {
            $table->uuid('clinic_visit_id')->primary(); // UUID 기본 키
            $table->uuid('apt_id'); // Appointment ID
            $table->string('emr_visit_no')->nullable();
            $table->string('procedure_code')->nullable();
            $table->decimal('charge_amount', 10, 2)->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            
            // 외래 키 제약 조건
            $table->foreign('apt_id')->references('apt_id')->on('appointments')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visits_clinics');
    }
};
