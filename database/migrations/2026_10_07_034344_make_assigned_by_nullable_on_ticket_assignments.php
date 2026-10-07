<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // null = assigned automatically by the system
        Schema::table('ticket_assignments', function (Blueprint $table) {
            $table->unsignedBigInteger('assigned_by')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('ticket_assignments', function (Blueprint $table) {
            $table->unsignedBigInteger('assigned_by')->nullable(false)->change();
        });
    }
};