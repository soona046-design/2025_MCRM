<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE leads MODIFY COLUMN status ENUM('new', 'contacted', 'scheduled', 'converted', 'pending', 'rejected') NOT NULL DEFAULT 'new'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("UPDATE leads SET status = 'converted' WHERE status = 'scheduled'");
        DB::statement("ALTER TABLE leads MODIFY COLUMN status ENUM('new', 'contacted', 'pending', 'converted', 'rejected') NOT NULL DEFAULT 'new'");
    }
};
