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
    Schema::create('leads', function (Blueprint $table) {
        $table->uuid('lead_id')->primary();
        $table->string('primary_phone')->nullable();
        $table->string('secondary_phone')->nullable();
        $table->string('email_hash')->nullable()->unique();
        $table->string('name')->nullable();
        $table->date('birth_date')->nullable();
        $table->enum('gender', ['male', 'female', 'other'])->nullable();
        $table->string('address')->nullable();
        $table->string('city')->nullable();
        $table->json('consent_flags')->nullable();
        $table->uuid('source_visit_id')->nullable(); // 외래키 제약 조건은 별도 마이그레이션에서 추가
        $table->enum('status', ['new', 'contacted', 'pending', 'converted', 'rejected'])->default('new');
        $table->integer('score')->default(0);
        $table->text('memo')->nullable();
        $table->uuid('latest_visit_id')->nullable();
        $table->uuid('latest_ticket_id')->nullable();
        $table->uuid('latest_appointment_id')->nullable();
        $table->uuid('assigned_user_id')->nullable();
        $table->timestamps();
    });
}

// ... existing code ...

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
