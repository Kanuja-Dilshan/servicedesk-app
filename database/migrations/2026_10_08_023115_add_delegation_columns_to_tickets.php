<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            // the IT Support user who handed the ticket to a technician (the ticket returns to them)
            $table->unsignedBigInteger('delegated_by')->nullable();
            // set when a technician finishes and sends the ticket back for review
            $table->timestamp('review_requested_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn(['delegated_by', 'review_requested_at']);
        });
    }
};