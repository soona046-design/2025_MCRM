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
        Schema::table('leads', function (Blueprint $table) {
            // visits 테이블과의 외래키 제약 조건
            $table->foreign('source_visit_id')->references('visit_id')->on('visits')->onDelete('set null');

            // users 테이블과의 외래키 제약 조건
            $table->foreign('assigned_user_id')->references('user_id')->on('users')->onDelete('set null');

            // tickets 테이블과의 외래키 제약 조건
            $table->foreign('latest_ticket_id')->references('ticket_id')->on('tickets')->onDelete('set null');

            // appointments 테이블과의 외래키 제약 조건
            $table->foreign('latest_appointment_id')->references('apt_id')->on('appointments')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropForeign(['source_visit_id']);
            $table->dropForeign(['assigned_user_id']);
            $table->dropForeign(['latest_ticket_id']);
            $table->dropForeign(['latest_appointment_id']);
        });
    }
};