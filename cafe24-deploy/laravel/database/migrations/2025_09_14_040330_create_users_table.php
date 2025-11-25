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
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('user_id')->primary(); // user_id (UUID 기본값 사용)
            $table->string('login_id')->unique();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken(); // remember_token 컬럼 추가
            $table->string('name')->nullable(); // name (nullable)
            $table->string('role')->default('상담매니저'); // role (기본값 '상담매니저')
            $table->string('clinic_id')->nullable(); // clinic_id (nullable)
            $table->string('phone')->nullable(); // phone (nullable)
            $table->string('two_fa_secret')->nullable(); // 2fa_secret (nullable)
            $table->boolean('active')->default(true); // active (기본값 true)
            $table->timestamps(); // created_at, updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
